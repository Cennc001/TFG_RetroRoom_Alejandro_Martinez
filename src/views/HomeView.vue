<script setup>
import { useAuthStore } from '../stores/auth.js'

const authStore = useAuthStore()
</script>

<template>
  <div class="home-container">
    <!-- HERO -->
    <section class="hero div-shadow">
      <div class="hero-content">
        <div class="game-title">
          <span class="pixel-art">RETROROOM</span>
        </div>

        <h2 class="tagline">
          <i class="bi bi-joystick me-2"></i>Arcade Interactivo | Chat Comunitario<i
            class="bi bi-chat-dots ms-2"
          ></i>
        </h2>

        <p class="description">
          Bienvenido a <strong>RetroRoom</strong>, un espacio nostálgico donde la pasión por los
          clásicos videojuegos se encuentra con la comunidad moderna. Aquí puedes jugar, competir y
          compartir tu propio código.
        </p>

        <!-- botones "First Steps" -->
        <div class="fs-buttons" v-if="!authStore.isAuthenticated">
          <router-link to="/registro" class="btn btn-primary btn-large">
            <i class="bi bi-play-circle me-2"></i>Registrarse
          </router-link>
          <router-link to="/login" class="btn btn-secondary btn-large">
            <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
          </router-link>
        </div>

        <div class="fs-buttons" v-else>
          <router-link to="/juego" class="btn btn-primary btn-large">
            <i class="bi bi-joystick me-2"></i>Jugar Ahora
          </router-link>
          <router-link to="/perfil" class="btn btn-secondary btn-large">
            <i class="bi bi-person-circle me-2"></i>Mi Perfil
          </router-link>
        </div>
      </div>

      <div class="hero-decoration">
        <div class="pixel-square"></div>
        <div class="pixel-square"></div>
        <div class="pixel-square"></div>
      </div>
    </section>

    <!-- SECCION - que puedes hacer -->
    <section class="features">
      <h3>¿Qué puedes hacer en RetroRoom?</h3>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-joystick"></i></div>
          <h4>Juegos Clásicos</h4>
          <p>
            Disfruta de juegos retro como Snake, Tetris y más. Compite por los mejores puntajes y
            sube en el ranking global.
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-chat-dots"></i></div>
          <h4>Chat Comunitario</h4>
          <p>Comunícate en tiempo real con otros jugadores. Comparte estrategias y haz amigos.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-floppy"></i></div>
          <h4>Sube Tu Código</h4>
          <p>
            ¿Tienes tu propio juego? Envía tu código JavaScript y HTML. Será revisado por
            moderadores para ser implementado en la plataforma.
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-trophy"></i></div>
          <h4>Sistema de Ranking</h4>
          <p>
            Compite globalmente y mantén un registro de tus mejores puntuaciones. Demuestra que eres
            el mejor jugador.
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-person-circle"></i></div>
          <h4>Perfil Personalizado</h4>
          <p>
            Crea tu perfil único con avatar personalizado. Muestra tus logros y estadísticas a toda
            la comunidad.
          </p>
        </div>
      </div>
    </section>

    <!-- SECCION - como funciona -->
    <section class="how-it-works">
      <h3>Cómo Funciona</h3>

      <div class="steps">
        <div class="step">
          <div class="step-number">1</div>
          <h4>Regístrate</h4>
          <p>Crea tu cuenta con un nombre de usuario y contraseña.</p>
        </div>

        <div class="step">
          <div class="step-number">2</div>
          <h4>Juega</h4>
          <p>Accede a los juegos disponibles y compite por los mejores puntajes.</p>
        </div>

        <div class="step">
          <div class="step-number">3</div>
          <h4>Comparte</h4>
          <p>Envía tu propio código JavaScript/HTML para que sea revisado.</p>
        </div>

        <div class="step">
          <div class="step-number">4</div>
          <h4>Conecta</h4>
          <p>Chatea con otros jugadores y forma parte de la comunidad.</p>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <section class="footer-fs div-shadow" v-if="!authStore.isAuthenticated">
      <h3>&iquest;Tienes tu propio juego?</h3>
      <p>Regístrate y comparte tu código JavaScript y HTML con la comunidad.</p>
      <router-link to="/registro" class="btn btn-primary btn-large">
        <i class="bi bi-play-circle me-2"></i>Registrarse Ahora
      </router-link>
    </section>

    <section class="footer-fs div-shadow" v-if="authStore.isAuthenticated">
      <h3>&iquest;Listo para compartir tu juego?</h3>
      <p>Envía tu código JavaScript y HTML para que otros puedan jugar.</p>
      <router-link to="/enviar-codigo" class="btn btn-primary btn-large">
        <i class="bi bi-cloud-upload me-2"></i>Enviar Mi Código
      </router-link>
    </section>
  </div>
</template>

<style scoped>
.home-container {
  width: 100%;
}

/* HERO */
.hero {
  position: relative;
  padding: 4rem 2rem;
  text-align: center;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
  border-radius: 12px;
  margin-bottom: 4rem;
  overflow: hidden;
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 800px;
  margin: 0 auto;
}

.game-title {
  margin-bottom: 1rem;
}

.pixel-art {
  font-size: 3.5rem;
  font-weight: 900;
  color: var(--color-heading);
  display: block;
  letter-spacing: 4px;
  text-transform: uppercase;
  font-family: 'Courier New', monospace;
  text-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0px);
  }

  50% {
    transform: translateY(-10px);
  }
}

.tagline {
  font-size: 1.8rem;
  color: var(--color-text);
  margin: 1rem 0 2rem;
  font-weight: 600;
}

.description {
  font-size: 1.1rem;
  color: var(--color-text);
  line-height: 1.8;
  margin-bottom: 2rem;
}

.fs-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.btn-large {
  padding: 0.875rem 2rem;
  font-size: 1rem;
  border-radius: 8px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  transition: all 0.3s ease;
  font-weight: 600;
}

.hero-decoration {
  position: absolute;
  top: 10%;
  right: 5%;
  display: flex;
  gap: 20px;
  opacity: 0.3;
  z-index: 1;
}

.pixel-square {
  width: 40px;
  height: 40px;
  background: #3b82f6;
  animation: rotate 4s linear infinite;
}

@keyframes rotate {
  0%,
  100% {
    transform: rotate(0deg);
  }

  50% {
    transform: rotate(45deg);
  }
}

/* SECCION caracteristicas */
.features {
  margin-bottom: 4rem;
}

.features h3,
.how-it-works h3,
.footer-fs h3 {
  font-size: 2.5rem;
  color: var(--color-heading);
  margin-bottom: 3rem;
  text-align: center;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.feature-card {
  background: var(--color-background-mute);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.feature-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(59, 130, 246, 0.15);
  border-color: #3b82f6;
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h4 {
  color: var(--color-heading);
  font-size: 1.3rem;
  margin-bottom: 0.75rem;
}

.feature-card p {
  color: var(--color-text);
  line-height: 1.6;
  margin: 0;
}

/* SECCION como funciona */
.how-it-works {
  margin-bottom: 4rem;
  background: rgba(59, 130, 246, 0.05);
  padding: 3rem 2rem;
  border-radius: 12px;
}

.steps {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
}

.step {
  text-align: center;
}

.step-number {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  margin: 0 auto 1rem;
}

.step h4 {
  color: var(--color-heading);
  font-size: 1.2rem;
  margin-bottom: 0.5rem;
}

.step p {
  color: var(--color-text);
  margin: 0;
}

/* FOOTER "First Steps" */
.footer-fs {
  text-align: center;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  padding: 3rem 2rem;
  border-radius: 12px;
  margin-bottom: 2rem;
}

.footer-fs h3 {
  color: white;
  margin-bottom: 1rem;
}

.footer-fs p {
  font-size: 1.1rem;
  margin-bottom: 2rem;
  color: rgba(255, 255, 255, 0.95);
}

.footer-fs .btn-primary {
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid white;
  color: white;
}

.footer-fs .btn-primary:hover {
  background: white;
  color: #3b82f6;
}

/* Responsive */
@media (max-width: 768px) {
  .hero {
    padding: 2rem 1rem;
  }

  .pixel-art {
    font-size: 2rem;
    letter-spacing: 2px;
  }

  .tagline {
    font-size: 1.3rem;
  }

  .features h3,
  .how-it-works h3,
  .footer-fs h3 {
    font-size: 1.8rem;
  }

  .features-grid,
  .steps {
    grid-template-columns: 1fr;
  }

  .fs-buttons {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-large {
    width: 100%;
    justify-content: center;
  }
}
</style>
