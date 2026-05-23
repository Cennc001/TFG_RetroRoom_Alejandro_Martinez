<template>
  <div class="ranking">
    <h2>Ranking de Jugadores</h2>
    <div class="game-selector">
      <label for="game-select">Selecciona un juego:</label>
      <select id="game-select" v-model="selectedGame" @change="fetchRanking">
        <option value="snake">Snake</option>
        <option value="tetris">Tetris</option>
      </select>
    </div>
    <div v-if="loading" class="loading">Cargando...</div>
    <div v-else-if="ranking.length === 0" class="no-data">
      No hay puntuaciones para este juego.
    </div>
    <div v-else class="ranking-list">
      <div v-for="(entry, index) in ranking" :key="index" class="ranking-entry">
        <span class="position">#{{ index + 1 }}</span>
        <img
          :src="entry.avatar_url || defaultAvatar"
          alt="Avatar"
          class="avatar me-2"
        />
        <span class="username">{{ entry.username }}</span>
        <span class="score-ranking mx-auto text-center">{{
          entry.puntuacion
        }}</span>
        <span class="date">{{ formatDate(entry.fecha) }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

export default {
  name: "Ranking",
  data() {
    return {
      selectedGame: "snake",
      ranking: [],
      loading: false,
      defaultAvatar: "https://cdn-icons-png.flaticon.com/512/149/149071.png",
    };
  },
  mounted() {
    this.fetchRanking();
  },
  methods: {
    // GET las puntiaciones del juego seleccionado
    fetchRanking() {
      this.loading = true;
      fetch(authStore.apiUrl(`get_ranking.php?juego=${this.selectedGame}`))
        .then((response) => response.json())
        .then((data) => {
          if (data.error) {
            // console.error('Error al obtener ranking:', data.error)
            this.ranking = [];
          } else {
            this.ranking = data;
          }
          this.loading = false;
        })
        .catch((error) => {
          // console.error('Error en la petición:', error)
          this.ranking = [];
          this.loading = false;
        });
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return (
        date.toLocaleDateString("es-ES") +
        " " +
        date.toLocaleTimeString("es-ES")
      );
    },
  },
};
</script>

<style scoped>
.ranking {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.game-selector {
  margin-bottom: 20px;
}

.game-selector label {
  margin-right: 10px;
  font-weight: bold;
}

.game-selector select {
  padding: 5px;
  font-size: 16px;
}

.loading,
.no-data {
  text-align: center;
  font-size: 18px;
  margin: 20px 0;
}

.ranking-list {
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid var(--color-border);
}

/* Entradas */
.ranking-entry {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 15px;
  border-bottom: 1px solid var(--color-border);
  background-color: var(--color-background-soft);
  transition:
    transform 0.15s ease,
    box-shadow 0.15s ease;
}

.ranking-entry:nth-child(even) {
  background-color: var(--color-background);
}

.ranking-entry:last-child {
  border-bottom: none;
}

/* HOVER */
.ranking-entry:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px var(--color-shadow);
}

/* AVATAR */
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--color-border);
}

/* POSICION */
.position {
  font-weight: bold;
  min-width: 40px;
  color: var(--color-text);
}

/* USUARIO */
.username {
  flex: 1;
  text-align: left;
  font-weight: 500;
  color: var(--color-text);
}

/* SCORE */
.score-ranking {
  font-weight: bold;
  color: var(--color-success);
  min-width: 70px;
  text-align: right;
}

/* FECHA */
.date {
  font-size: 12px;
  color: var(--color-text-muted);
  min-width: 110px;
  text-align: right;
}

.ranking-entry:nth-child(1) {
  background: linear-gradient(
    90deg,
    var(--color-gold-light),
    var(--color-gold)
  );
}

.ranking-entry:nth-child(1) .avatar {
  border: 2px solid var(--color-gold);
}

.ranking-entry:nth-child(1) .position {
  color: var(--color-gold-dark);
  font-size: 18px;
}

.ranking-entry:nth-child(2) {
  background: linear-gradient(
    90deg,
    var(--color-silver-light),
    var(--color-silver)
  );
}

.ranking-entry:nth-child(2) .avatar {
  border: 2px solid var(--color-silver);
}

.ranking-entry:nth-child(2) .position {
  color: var(--color-silver-dark);
  font-size: 17px;
}
</style>
