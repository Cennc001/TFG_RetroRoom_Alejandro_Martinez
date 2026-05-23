<template>
  <div class="stick-hero-game">
    <h2>Stick Hero</h2>
    <p>Este juego todavía está en desarrollo. Pronto estará disponible.</p>
  </div>
</template>

<script>
export default {
  name: 'StickHeroGame',
}
</script>

<style scoped>
.stick-hero-game {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  color: var(--color-text);
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 2rem;
}

.stick-hero-game h2 {
  color: var(--color-heading);
  margin-bottom: 1rem;
}

.stick-hero-game p {
  color: var(--color-text);
  text-align: center;
}
</style>
