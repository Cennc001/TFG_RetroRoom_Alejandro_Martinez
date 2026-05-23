<template>
  <div class="games-container">
    <!-- Header -->
    <div class="games-header mb-4">
      <div
        class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3"
      >
        <div>
          <h1>Arcade de Juegos</h1>
          <p class="subtitle">Elige tu juego y demuestra tu habilidad</p>
        </div>
        <button
          @click="toggleViewMode"
          type="button"
          class="btn btn-outline-primary btn-sm"
        >
          <i class="bi bi-list"></i>
          <span class="ms-2">{{
            viewMode === "expanded" ? "Colapsar" : "Expandir"
          }}</span>
        </button>
      </div>
    </div>

    <!-- Selector de juego -->
    <div
      :class="[
        'games-grid',
        'mb-4',
        'div-shadow',
        { 'collapsed-layout': viewMode === 'collapsed' },
      ]"
    >
      <button
        v-for="game in games"
        :key="game.id"
        @click="currentGame = game.id"
        :class="[
          'juego-card',
          game.bgClass,
          {
            active: currentGame === game.id,
            collapsed: viewMode === 'collapsed',
          },
        ]"
        :title="game.title + ' - ' + game.description"
      >
        <div class="game-icon">
          <i :class="game.icon"></i>
        </div>
        <h3>{{ game.title }}</h3>
        <p>{{ game.description }}</p>
        <small class="game-credit"
          >Creado por {{ game.creator }} · {{ game.merit }}</small
        >
      </button>
    </div>

    <!-- Contenido del Juego -->
    <div class="game-content">
      <SnakeGame v-if="currentGame === 'snake'" />
      <TetrisGame v-if="currentGame === 'tetris'" />
      <Ranking v-if="currentGame === 'ranking'" />
      <StickHeroGame v-if="currentGame === 'stickHero'" />
    </div>
  </div>
</template>

<script>
import SnakeGame from "@/components/SnakeGame.vue";
import TetrisGame from "@/components/TetrisGame.vue";
import Ranking from "@/components/Ranking.vue";
import StickHeroGame from "@/components/StickHero.vue";

export default {
  name: "GameView",
  // componentes de juegos
  components: {
    SnakeGame,
    TetrisGame,
    Ranking,
    StickHeroGame,
  },
  data() {
    // estado del juego actual y lista de juegos
    return {
      currentGame: "snake",
      viewMode: "expanded",
      games: [
        {
          id: "ranking",
          title: "Ranking",
          description: "Top jugadores del servidor",
          icon: "bi bi-trophy",
          bgClass: "ranking-card",
          creator: "Comunidad RetroRoom",
          merit: "Méritos para la competencia",
        },
        {
          id: "snake",
          title: "Snake",
          description: "Come, crece y evita chocar",
          icon: "bi bi-cursor",
          bgClass: "snake-card",
          creator: "Equipo RetroRoom",
          merit: "Diseño clásico de arcade",
        },
        {
          id: "tetris",
          title: "Tetris",
          description: "Completa líneas horizontales",
          icon: "bi bi-square",
          bgClass: "tetris-card",
          creator: "Equipo RetroRoom",
          merit: "Puzzle retro atemporal",
        },
        {
          id: "stickHero",
          title: "Stick Hero",
          description: "Salta entre plataformas",
          icon: "bi bi-bar-chart",
          bgClass: "stickHero-card",
          creator: "Equipo RetroRoom",
          merit: "Juego de habilidad pixelado",
        },
      ],
    };
  },
  watch: {
    // hacer scroll al juego seleccionado
    currentGame() {
      this.$nextTick(() => {
        const gameContent = document.querySelector(".game-content");
        if (gameContent) {
          gameContent.scrollIntoView({
            behavior: "smooth",
            block: "start",
            inline: "nearest",
          });
        }
      });
    },
  },
  mounted() {
    window.addEventListener("keydown", this.preventGameScroll);
  },
  beforeUnmount() {
    window.removeEventListener("keydown", this.preventGameScroll);
  },
  methods: {
    // alternar el modo de vista entre expandido y colapsado
    toggleViewMode() {
      this.viewMode = this.viewMode === "expanded" ? "collapsed" : "expanded";
    },
    // evitar que las teclas de juego hagan scroll en la página
    preventGameScroll(event) {
      const target = event.target;
      const ignoredTags = ["INPUT", "TEXTAREA", "SELECT"];
      const isEditable =
        ignoredTags.includes(target.tagName) || target.isContentEditable;
      if (isEditable) {
        return;
      }

      const gameKeys = ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight", " "];
      const playableGame = this.currentGame !== "ranking";
      if (playableGame && gameKeys.includes(event.key)) {
        event.preventDefault();
      }
    },
  },
};
</script>

<style scoped>
.games-container {
  padding: 2rem 0;
  max-width: 1400px;
  margin: 0 auto;
}

.games-header {
  text-align: center;
  margin-bottom: 3rem;
}

.games-header h1 {
  font-size: 3rem;
  color: var(--color-heading);
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
}

.subtitle {
  font-size: 1.1rem;
  color: var(--color-muted);
}

.games-grid {
  display: flex;
  flex-wrap: nowrap;
  gap: 1.5rem;
  overflow-x: auto;
  overflow-y: hidden;
  padding-bottom: 0.5rem;
  scroll-behavior: smooth;
  padding: 20px 20px;
  border-radius: 20px;
  transition: all 0.5s ease;
}

.games-grid.collapsed-layout {
  flex-wrap: wrap;
  justify-content: flex-start;
  gap: 1rem;
}

.games-grid.collapsed-layout .juego-card {
  min-width: 180px;
  min-height: 140px;
}

.games-grid::-webkit-scrollbar {
  height: 8px;
}

.games-grid::-webkit-scrollbar-track {
  background: var(--color-background-mute);
  border-radius: 10px;
}

.games-grid::-webkit-scrollbar-thumb {
  background: var(--color-muted);
  border-radius: 10px;
}

.games-grid::-webkit-scrollbar-thumb:hover {
  background: var(--color-border-hover);
}

.juego-card {
  position: relative;
  overflow: hidden;
  border: none;
  border-radius: 12px;
  padding: 1.5rem;
  color: white;
  cursor: pointer;
  transition:
    all 0.5s ease,
    transform 0.5s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  min-height: 220px;
  min-width: 250px;
  font-family: inherit;
  font-weight: 600;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  flex-shrink: 0;
}

.juego-card.collapsed {
  min-height: 120px;
  min-width: 160px;
  padding: 1rem;
}

.juego-card.collapsed .game-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.juego-card.collapsed h3 {
  font-size: 1rem;
  margin-bottom: 0;
}

.juego-card .game-icon,
.juego-card h3,
.juego-card p,
.juego-card .game-credit {
  transition: all 0.4s ease;
}

.juego-card.collapsed p,
.juego-card.collapsed .game-credit {
  opacity: 0;
  max-height: 0;
  margin: 0;
  padding: 0;
  overflow: hidden;
}

.juego-card.collapsed .game-icon {
  transform: scale(0.85);
}

.juego-card.collapsed h3 {
  font-size: 1rem;
  margin-bottom: 0;
}

.juego-card.collapsed {
  max-height: 110px;
  max-width: 140px;
  padding: 1rem;
}

.juego-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
  z-index: 1;
}

.juego-card:hover::before {
  background: rgba(0, 0, 0, 0.1);
}

.juego-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
}

.juego-card.active {
  transform: scale(1.05);
  box-shadow: 0 0 30px rgba(59, 130, 246, 0.5);
}

.juego-card.active::after {
  content: "";
  position: absolute;
  top: -2px;
  right: -2px;
  bottom: -2px;
  left: -2px;
  background: linear-gradient(45deg, #3b82f6, #06b6d4);
  border-radius: 12px;
  z-index: -1;
}

.game-icon {
  position: relative;
  z-index: 2;
  font-size: 3.5rem;
  margin-bottom: 1rem;
  opacity: 0.9;
  transition: all 0.4s ease;
}

.juego-card h3 {
  position: relative;
  z-index: 2;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  margin: 0 0 0.5rem 0;
  transition: all 0.4s ease;
}

.juego-card p,
.juego-card .game-credit {
  position: relative;
  z-index: 2;
  font-size: 0.9rem;
  margin: 0;
  opacity: 0.95;
  max-height: 60px;
  transition: all 0.4s ease;
}

.game-credit {
  position: relative;
  z-index: 2;
  display: block;
  margin-top: 1rem;
  font-size: 0.78rem;
  opacity: 0.8;
  color: rgba(255, 255, 255, 0.85);
}

/* Gradient backgrounds para cada juego */
.snake-card {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.tetris-card {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.ranking-card {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
}

.stickHero-card {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.game-content {
  background: var(--color-background-soft);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  min-height: 420px;
  display: flex;
  flex-direction: column;
  overflow: visible;
}

@media (max-width: 768px) {
  .games-container {
    padding: 1rem;
  }

  .games-header h1 {
    font-size: 2rem;
  }

  .games-grid {
    gap: 1rem;
  }

  .juego-card {
    min-height: 180px;
    min-width: 150px;
    padding: 1rem;
  }

  .game-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
  }

  .juego-card h3 {
    font-size: 1.1rem;
  }

  .juego-card p {
    font-size: 0.8rem;
  }

  .game-content {
    padding: 1rem;
  }
}
</style>
