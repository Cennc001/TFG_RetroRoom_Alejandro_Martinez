<template>
  <div class="game-shell">
    <h2>Tetris</h2>
    <div class="game-info">
      <div class="score">Puntuación: {{ score }}</div>
      <div class="level">Nivel: {{ level }}</div>
      <div class="lines">Líneas: {{ lines }}</div>
    </div>
    <div class="game-area">
      <div class="canvas-container">
        <canvas
          ref="canvas"
          width="300"
          height="600"
          class="game-canvas"
        ></canvas>
        <div v-if="!gameStarted" class="start-screen">
          <p>Presiona cualquier flecha para comenzar</p>
        </div>
        <div v-if="gameOver" class="game-over">
          <p>Juego Terminado</p>
          <p>Puntuación final: {{ score }}</p>
          <button @click="resetGame">Reiniciar</button>
        </div>
      </div>
      <div class="next-piece">
        <h3>Siguiente:</h3>
        <canvas
          ref="nextCanvas"
          width="100"
          height="100"
          class="next-canvas"
        ></canvas>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "@/stores/auth";

//
// Este es el segundo juego que hago, aqui no me paro en logica de draw y loop de update
//     pero tiene logica de rotacion, colisiones solo abajo pero con skip y laterales, aparte del merge de bloques
//
export default {
  name: "TetrisGame",
  data() {
    return {
      canvas: null,
      ctx: null,
      nextCanvas: null,
      nextCtx: null,
      board: [],
      // currentPiece y nextPiece { shape, color, x, y, rotation }
      currentPiece: null,
      nextPiece: null,
      score: 0,
      level: 1,
      lines: 0,
      gameOver: false,
      gameStarted: false,
      gameLoop: null,
      dropTime: 0,
      dropInterval: 1000,
      sentScore: false,
      // colores de las piezas
      // en orden : ROJO, VERDE   , AZUL   , VERDE OSCURO, MAGENTA, AZUL OSCURO, NARANJA
      colors: [
        "#FF0000",
        "#00FF00",
        "#0000FF",
        "#005800",
        "#FF00FF",
        "#00D8D8",
        "#FFA500",
      ],
      // piezas y cada una de sus rotaciones
      pieces: [
        // I
        [[[1, 1, 1, 1]], [[1], [1], [1], [1]]],
        // O
        [
          [
            [1, 1],
            [1, 1],
          ],
        ],
        // T
        [
          [
            [0, 1, 0],
            [1, 1, 1],
          ],
          [
            [1, 0],
            [1, 1],
            [1, 0],
          ],
          [
            [1, 1, 1],
            [0, 1, 0],
          ],
          [
            [0, 1],
            [1, 1],
            [0, 1],
          ],
        ],
        // S
        [
          [
            [0, 1, 1],
            [1, 1, 0],
          ],
          [
            [1, 0],
            [1, 1],
            [0, 1],
          ],
        ],
        // Z
        [
          [
            [1, 1, 0],
            [0, 1, 1],
          ],
          [
            [0, 1],
            [1, 1],
            [1, 0],
          ],
        ],
        // J
        [
          [
            [1, 0, 0],
            [1, 1, 1],
          ],
          [
            [1, 1],
            [1, 0],
            [1, 0],
          ],
          [
            [1, 1, 1],
            [0, 0, 1],
          ],
          [
            [0, 1],
            [0, 1],
            [1, 1],
          ],
        ],
        // L
        [
          [
            [0, 0, 1],
            [1, 1, 1],
          ],
          [
            [1, 0],
            [1, 0],
            [1, 1],
          ],
          [
            [1, 1, 1],
            [1, 0, 0],
          ],
          [
            [1, 1],
            [0, 1],
            [0, 1],
          ],
        ],
      ],
    };
  },
  // LOAD y UNLOAD - canvas y listeners
  mounted() {
    this.canvas = this.$refs.canvas;
    this.ctx = this.canvas.getContext("2d");
    this.nextCanvas = this.$refs.nextCanvas;
    this.nextCtx = this.nextCanvas.getContext("2d");
    this.initBoard();
    this.generateNextPiece();
    this.addEventListeners();
  },
  beforeUnmount() {
    this.stopGame();
    this.removeEventListeners();
  },
  methods: {
    initBoard() {
      this.board = Array(20)
        .fill()
        .map(() => Array(10).fill(0));
    },
    // PIEZAS - siguiente pieza, spawnear pieza actual, colisiones, merge, clear lines, rotar y mover
    generateNextPiece() {
      const pieceIndex = Math.floor(Math.random() * this.pieces.length);
      this.nextPiece = {
        shape: this.pieces[pieceIndex][0],
        color: this.colors[pieceIndex],
        x: 3,
        y: 0,
        rotation: 0,
      };
    },
    // PIEZAS - sacar siguiente
    spawnPiece() {
      this.currentPiece = { ...this.nextPiece };
      this.generateNextPiece();
      if (this.collision()) {
        this.gameOver = true;
        if (!this.sentScore) {
          this.sendScore();
          this.sentScore = true;
        }
      }
    },
    // aqui he cogido de este repositorio la colision porque ni idea de como montarla
    // https://gist.github.com/straker/3c98304f8a6a9174efd8292800891ea1
    collision(piece = this.currentPiece) {
      for (let y = 0; y < piece.shape.length; y++) {
        for (let x = 0; x < piece.shape[y].length; x++) {
          if (piece.shape[y][x] !== 0) {
            const newX = piece.x + x;
            const newY = piece.y + y;
            if (
              newX < 0 ||
              newX >= 10 ||
              newY >= 20 ||
              (newY >= 0 && this.board[newY][newX] !== 0)
            ) {
              return true;
            }
          }
        }
      }
      return false;
    },
    // MERGE - solo compruebo si toda la fila es "1"
    merge() {
      for (let y = 0; y < this.currentPiece.shape.length; y++) {
        for (let x = 0; x < this.currentPiece.shape[y].length; x++) {
          if (this.currentPiece.shape[y][x] !== 0) {
            this.board[this.currentPiece.y + y][this.currentPiece.x + x] =
              this.currentPiece.color;
          }
        }
      }
    },
    // MERGE - limpieza de filas y puntuacion escalable
    clearLines() {
      let linesCleared = 0;
      for (let y = this.board.length - 1; y >= 0; y--) {
        if (this.board[y].every((cell) => cell !== 0)) {
          this.board.splice(y, 1);
          this.board.unshift(Array(10).fill(0));
          linesCleared++;
          y++;
        }
      }
      if (linesCleared > 0) {
        this.lines += linesCleared;
        this.score += linesCleared * 100 * this.level;
        this.level = Math.floor(this.lines / 10) + 1;
        this.dropInterval = Math.max(50, 1000 - (this.level - 1) * 50);
      }
    },
    // MOVIMIENTO - no solo cambia de array, si colisiona, evita rotacion
    rotate() {
      const rotated = this.pieces[this.colors.indexOf(this.currentPiece.color)];
      this.currentPiece.rotation =
        (this.currentPiece.rotation + 1) % rotated.length;
      const oldShape = this.currentPiece.shape;
      this.currentPiece.shape = rotated[this.currentPiece.rotation];
      if (this.collision()) {
        this.currentPiece.shape = oldShape;
        this.currentPiece.rotation =
          (this.currentPiece.rotation - 1 + rotated.length) % rotated.length;
      }
    },
    // MOVIMIENTO - general
    move(dir) {
      this.currentPiece.x += dir;
      if (this.collision()) {
        this.currentPiece.x -= dir;
      }
    },
    // MOVIMIENTO - bajar
    drop() {
      this.currentPiece.y++;
      if (this.collision()) {
        this.currentPiece.y--;
        this.merge();
        this.clearLines();
        this.spawnPiece();
      }
    },
    // MOVIMIENTO - skip
    dropSkip() {
      while (!this.collision()) {
        this.currentPiece.y++;
      }
      this.currentPiece.y--;
      this.merge();
      this.clearLines();
      this.spawnPiece();
      this.score += 2;
    },
    startGame() {
      this.gameLoop = requestAnimationFrame(this.gameStep);
    },
    stopGame() {
      if (this.gameLoop) {
        cancelAnimationFrame(this.gameLoop);
        this.gameLoop = null;
      }
    },
    // GAME LOOP
    gameStep(timestamp) {
      if (this.gameOver || !this.gameStarted) return;
      if (timestamp - this.dropTime >= this.dropInterval) {
        this.drop();
        this.dropTime = timestamp;
      }
      this.draw();
      this.gameLoop = requestAnimationFrame(this.gameStep);
    },
    draw() {
      this.ctx.clearRect(0, 0, 300, 600);
      this.nextCtx.clearRect(0, 0, 100, 100);

      // Colores
      const drawBlock = (ctx, x, y, size, color) => {
        const gradient = ctx.createLinearGradient(x, y, x + size, y + size);
        gradient.addColorStop(0, color);
        gradient.addColorStop(0.5, this.lightenColor(color, 40)); // con mas brillo en centro
        gradient.addColorStop(1, color);

        ctx.fillStyle = gradient;
        ctx.fillRect(x, y, size, size);

        // Borde exterior
        ctx.strokeStyle = this.lightenColor(color, 60);
        ctx.lineWidth = 2;
        ctx.strokeRect(x, y, size, size);

        // Borde interior
        ctx.strokeStyle = this.lightenColor(color, 30);
        ctx.lineWidth = 1;
        ctx.strokeRect(x + 2, y + 2, size - 4, size - 4);
      };

      // DRAW - tablero
      for (let y = 0; y < this.board.length; y++) {
        for (let x = 0; x < this.board[y].length; x++) {
          if (this.board[y][x] !== 0) {
            drawBlock(this.ctx, x * 30, y * 30, 30, this.board[y][x]);
          }
        }
      }

      // DRAW - pieza actual
      if (this.currentPiece) {
        for (let y = 0; y < this.currentPiece.shape.length; y++) {
          for (let x = 0; x < this.currentPiece.shape[y].length; x++) {
            if (this.currentPiece.shape[y][x] !== 0) {
              drawBlock(
                this.ctx,
                (this.currentPiece.x + x) * 30,
                (this.currentPiece.y + y) * 30,
                30,
                this.currentPiece.color,
              );
            }
          }
        }
      }

      // DRAW - siguiente pieza
      if (this.nextPiece) {
        for (let y = 0; y < this.nextPiece.shape.length; y++) {
          for (let x = 0; x < this.nextPiece.shape[y].length; x++) {
            if (this.nextPiece.shape[y][x] !== 0) {
              drawBlock(
                this.nextCtx,
                x * 20 + 20,
                y * 20 + 20,
                20,
                this.nextPiece.color,
              );
            }
          }
        }
      }

      // DRAW - tablero grid
      this.ctx.strokeStyle = "rgba(0, 255, 255, 0.1)";
      this.ctx.lineWidth = 1;
      for (let i = 0; i <= 10; i++) {
        this.ctx.beginPath();
        this.ctx.moveTo(i * 30, 0);
        this.ctx.lineTo(i * 30, 600);
        this.ctx.stroke();
      }
      for (let i = 0; i <= 20; i++) {
        this.ctx.beginPath();
        this.ctx.moveTo(0, i * 30);
        this.ctx.lineTo(300, i * 30);
        this.ctx.stroke();
      }
    },
    // Funcion auxiliar para aclarar colores
    lightenColor(color, percent) {
      const num = parseInt(color.replace("#", ""), 16);
      const amt = Math.round(2.55 * percent);
      const R = Math.min(255, (num >> 16) + amt);
      const G = Math.min(255, ((num >> 8) & 0x00ff) + amt);
      const B = Math.min(255, (num & 0x0000ff) + amt);
      return (
        "#" +
        (
          0x1000000 +
          (R < 255 ? R : 255) * 0x10000 +
          (G < 255 ? G : 255) * 0x100 +
          (B < 255 ? B : 255)
        )
          .toString(16)
          .slice(1)
      );
    },
    addEventListeners() {
      window.addEventListener("keydown", this.handleKeydown);
    },
    removeEventListeners() {
      window.removeEventListener("keydown", this.handleKeydown);
    },
    // MOVIMIENTO - listener de teclas
    handleKeydown(event) {
      const target = event.target;
      const ignoredTags = ["INPUT", "TEXTAREA", "SELECT"];
      const isEditable =
        ignoredTags.includes(target.tagName) || target.isContentEditable;
      if (isEditable) {
        return;
      }

      if (this.gameOver) return;
      if (!this.gameStarted) {
        this.gameStarted = true;
        this.spawnPiece();
        this.startGame();
        return;
      }
      switch (event.key) {
        case "ArrowLeft":
          event.preventDefault();
          this.move(-1);
          break;
        case "ArrowRight":
          event.preventDefault();
          this.move(1);
          break;
        case "ArrowDown":
          event.preventDefault();
          this.drop();
          this.score += 1;
          break;
        case "ArrowUp":
          event.preventDefault();
          this.rotate();
          break;
        case " ": // Espacio
          event.preventDefault();
          this.dropSkip();
          break;
      }
    },
    resetGame() {
      this.stopGame();
      this.initBoard();
      this.currentPiece = null;
      this.generateNextPiece();
      this.score = 0;
      this.level = 1;
      this.lines = 0;
      this.gameOver = false;
      this.gameStarted = false;
      this.dropTime = 0;
      this.dropInterval = 1000;
      this.sentScore = false;
    },
    sendScore() {
      const authStore = useAuthStore();

      // Check si el usuario esta autenticado antes de guardar
      if (!authStore.isAuthenticated) {
        console.warn(
          "Usuario no autenticado. No se puede guardar la puntuación.",
        );
        return;
      }

      fetch(authStore.apiUrl("save_score.php"), {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: "Bearer " + authStore.token,
        },
        body: JSON.stringify({
          juego: "tetris",
          puntuacion: this.score,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.error) {
            // console.error('Error al guardar puntuación:', data.error)
          } else {
            // console.log('Puntuación guardada:', data.mensaje)
          }
        })
        .catch((error) => {
          //  console.error('Error en la petición:', error)
        });
    },
  },
};
</script>

<style scoped>
.game-info {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  justify-content: center;
}

.next-piece {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  background: linear-gradient(
    135deg,
    rgba(0, 255, 255, 0.1),
    rgba(255, 0, 255, 0.1)
  );
  border: 2px solid #007c7c;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
}

.next-piece h3 {
  margin: 0 0 15px 0;
  font-size: 1.1rem;
  color: #00ffff;
  text-shadow: 0 0 10px rgba(0, 255, 255, 0.8);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.next-canvas {
  background: #181818;
  border: 1px solid rgba(0, 255, 255, 0.3);
  border-radius: 8px;
}
</style>
