const game = document.getElementById('game')
const player = document.getElementById('player')
const target = document.getElementById('target')
const scoreText = document.getElementById('score')

const gameWidth = 600
const gameHeight = 400

let playerX = 280
let playerY = 180

let targetX = 100
let targetY = 100

let score = 0
const speed = 10

function movePlayer() {
  player.style.left = playerX + 'px'
  player.style.top = playerY + 'px'
}

function moveTarget() {
  target.style.left = targetX + 'px'
  target.style.top = targetY + 'px'
}

function randomizeTarget() {
  targetX = Math.random() * (gameWidth - 30)
  targetY = Math.random() * (gameHeight - 30)

  moveTarget()
}

function checkCollision() {
  const playerRect = player.getBoundingClientRect()
  const targetRect = target.getBoundingClientRect()

  if (
    playerRect.left < targetRect.right &&
    playerRect.right > targetRect.left &&
    playerRect.top < targetRect.bottom &&
    playerRect.bottom > targetRect.top
  ) {
    score++
    scoreText.textContent = 'Puntos: ' + score
    randomizeTarget()
  }
}

document.addEventListener('keydown', (e) => {
  switch (e.key) {
    case 'ArrowUp':
      playerY -= speed
      break

    case 'ArrowDown':
      playerY += speed
      break

    case 'ArrowLeft':
      playerX -= speed
      break

    case 'ArrowRight':
      playerX += speed
      break
  }

  playerX = Math.max(0, Math.min(gameWidth - 40, playerX))
  playerY = Math.max(0, Math.min(gameHeight - 40, playerY))

  movePlayer()
  checkCollision()
})

movePlayer()
moveTarget()
