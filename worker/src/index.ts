import { loadConfig } from './config.js'

const config = loadConfig()

console.log(
  JSON.stringify({
    level: config.LOG_LEVEL,
    event: 'worker_configured',
    worker_id: config.WORKER_ID,
  }),
)
