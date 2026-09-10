import { Worker } from './worker.js'

const worker = new Worker()

worker.start().catch((error) => {
  console.error(
    JSON.stringify({
      level: 'error',
      event: 'worker_fatal_error',
      error: error instanceof Error ? error.message : String(error),
    }),
  )
  process.exit(1)
})
