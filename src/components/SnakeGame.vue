<template>
  <div class="snake-game game-shell">
    <h2>Snake Game</h2>
    <div class="score">Puntuación: {{ score }}</div>
    <div class="canvas-container">
      <canvas
        ref="canvas"
        width="400"
        height="400"
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
  </div>
</template>

<script>
import { useAuthStore } from "@/stores/auth";

// Esta todo comentado, con categorias porque no tenia ni idea de como hacer un
//   canvas con game loop, asique entre papa stack overflow con el siguiente link:
//   https://stackoverflow.com/questions/19614329/snake-game-in-javascript (de 2013, he tenido que actualizar y reformatear)
//   pero he cogido la logica base, movimiento, comida y el loop

export default {
  name: "SnakeGame",
  data() {
    return {
      canvas: null,
      ctx: null,
      snake: [{ x: 200, y: 200 }],
      direction: { x: 0, y: 0 },
      food: [],
      score: 0,
      gameOver: false,
      gameStarted: false,
      gameLoop: null,
      speed: 150, // ms por movimiento
      lastUpdate: 0,
      debug: { debugComidaRepeat: 50 }, // multiplicador de comida (por defecto 1)
      sentScore: false,
    };
  },
  mounted() {
    this.canvas = this.$refs.canvas;
    this.ctx = this.canvas.getContext("2d");
    this.generateFood();
    this.addEventListeners();
  },
  // UNLOAD - quitar los listeners
  beforeUnmount() {
    this.stopGame();
    this.removeEventListeners();
  },
  methods: {
    // GAME CONTROL
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
      if (timestamp - this.lastUpdate >= this.speed) {
        this.update();
        this.draw();
        this.lastUpdate = timestamp;
      }
      this.gameLoop = requestAnimationFrame(this.gameStep);
    },
    // UPDATE - movimiento
    update() {
      const head = { ...this.snake[0] };
      head.x += this.direction.x * 20;
      head.y += this.direction.y * 20;

      // COLISION - paredes
      if (head.x < 0 || head.x >= 400 || head.y < 0 || head.y >= 400) {
        this.gameOver = true;
        if (!this.sentScore) {
          this.sendScore();
          this.sentScore = true;
        }
        return;
      }

      // COLISION - cuerpo
      for (let segment of this.snake) {
        if (head.x === segment.x && head.y === segment.y) {
          this.gameOver = true;
          if (!this.sentScore) {
            this.sendScore();
            this.sentScore = true;
          }
          return;
        }
      }
      // MOVIMIENTO - nueva cabeza
      this.snake.unshift(head);

      // COMIDA - crecer
      const foodIndex = this.food.findIndex(
        (f) => f.x === head.x && f.y === head.y,
      );
      if (foodIndex !== -1) {
        this.score += 10;
        this.food.splice(foodIndex, 1);
        this.generateFood();
      } else {
        this.snake.pop();
      }
    },
    //
    // DRAW
    //
    draw() {
      this.ctx.clearRect(0, 0, 400, 400);

      // DRAW - grid de fondo
      this.ctx.strokeStyle = "rgba(0, 255, 255, 0.15)";
      this.ctx.lineWidth = 1;
      for (let i = 0; i <= 400; i += 20) {
        this.ctx.beginPath();
        this.ctx.moveTo(i, 0);
        this.ctx.lineTo(i, 400);
        this.ctx.stroke();
        this.ctx.beginPath();
        this.ctx.moveTo(0, i);
        this.ctx.lineTo(400, i);
        this.ctx.stroke();
      }

      // DRAW - serpiente
      for (let i = 0; i < this.snake.length; i++) {
        const segment = this.snake[i];
        const isHead = i === 0;

        // Crear gradiente para cada segmento
        const gradient = this.ctx.createLinearGradient(
          segment.x,
          segment.y,
          segment.x + 20,
          segment.y + 20,
        );
        if (isHead) {
          gradient.addColorStop(0, "#00ff88");
          gradient.addColorStop(1, "#00ccff");
        } else {
          gradient.addColorStop(0, "#00dd77");
          gradient.addColorStop(1, "#00bb88");
        }

        // Dibujar el cuadro del segmento
        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(segment.x, segment.y, 20, 20);

        // Borde
        this.ctx.strokeStyle = isHead ? "#00ffff" : "#00ff88";
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(segment.x, segment.y, 20, 20);

        // Efecto interior con mas brillo
        this.ctx.strokeStyle = isHead
          ? "rgba(0, 255, 255, 0.6)"
          : "rgba(0, 255, 136, 0.4)";
        this.ctx.lineWidth = 1;
        this.ctx.strokeRect(segment.x + 2, segment.y + 2, 16, 16);
      }

      // DRAW - comida con efecto de brillo
      for (let f of this.food) {
        // Gradiente radial para efecto de brillo
        const foodGradient = this.ctx.createRadialGradient(
          f.x + 10,
          f.y + 10,
          0,
          f.x + 10,
          f.y + 10,
          14,
        );
        foodGradient.addColorStop(0, "rgba(255, 100, 100)");
        foodGradient.addColorStop(1, "rgba(255, 50, 50, 0.5)");

        this.ctx.fillStyle = foodGradient;
        this.ctx.beginPath();
        this.ctx.arc(f.x + 10, f.y + 10, 10, 0, Math.PI * 2);
        this.ctx.fill();

        // Borde de comida
        this.ctx.strokeStyle = "#ff6666";
        this.ctx.lineWidth = 2;
        this.ctx.beginPath();
        this.ctx.stroke();
      }
    },
    // COMIDA - generar comida
    generateFood() {
      while (
        this.food.length < this.debug.debugComidaRepeat ||
        this.food.length < 1
      ) {
        let newFood;
        do {
          // posicion
          newFood = {
            x: Math.floor(Math.random() * 20) * 20,
            y: Math.floor(Math.random() * 20) * 20,
          };
        } while (
          // evitar generar comida encima de la serpiente o de otra comida
          this.snake.some(
            (segment) => segment.x === newFood.x && segment.y === newFood.y,
          ) ||
          this.food.some((f) => f.x === newFood.x && f.y === newFood.y)
        );
        this.food.push(newFood);
      }
    },
    //
    // CONTROLES
    //
    addEventListeners() {
      window.addEventListener("keydown", this.handleKeydown);
    },
    removeEventListeners() {
      window.removeEventListener("keydown", this.handleKeydown);
    },
    handleKeydown(event) {
      const target = event.target;
      const ignoredTags = ["INPUT", "TEXTAREA", "SELECT"];
      const isEditable =
        ignoredTags.includes(target.tagName) || target.isContentEditable;
      if (isEditable) {
        return;
      }

      if (this.gameOver) return;
      let directionChanged = false;
      switch (event.key) {
        case "ArrowUp":
        case "ArrowDown":
        case "ArrowLeft":
        case "ArrowRight":
          event.preventDefault();
          break;
      }
      switch (event.key) {
        case "ArrowUp":
          if (this.direction.y === 0) {
            this.direction = { x: 0, y: -1 };
            directionChanged = true;
          }
          break;
        case "ArrowDown":
          if (this.direction.y === 0) {
            this.direction = { x: 0, y: 1 };
            directionChanged = true;
          }
          break;
        case "ArrowLeft":
          if (this.direction.x === 0) {
            this.direction = { x: -1, y: 0 };
            directionChanged = true;
          }
          break;
        case "ArrowRight":
          if (this.direction.x === 0) {
            this.direction = { x: 1, y: 0 };
            directionChanged = true;
          }
          break;
      }
      if (directionChanged && !this.gameStarted) {
        this.gameStarted = true;
        this.startGame();
      }
    },
    resetGame() {
      this.stopGame();
      this.snake = [{ x: 200, y: 200 }];
      this.direction = { x: 0, y: 0 };
      this.score = 0;
      this.gameOver = false;
      this.gameStarted = false;
      this.lastUpdate = 0;
      this.sentScore = false;
      this.generateFood();
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

      // POST la puntuacion al backend
      fetch(authStore.apiUrl("save_score.php"), {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: "Bearer " + authStore.token,
        },
        body: JSON.stringify({
          juego: "snake",
          puntuacion: this.score,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.error) {
            // console.error("Error al guardar puntuación:", data.error);
          }
        })
        .catch((error) => {
          // console.error("Error en la petición:", error);
        });
    },
  },
};
</script>

<style scoped></style>
