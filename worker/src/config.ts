import { z } from 'zod'

const configSchema = z.object({
  NODE_ENV: z
    .enum(['development', 'test', 'production'])
    .default('development'),
  WORKER_ID: z.string().min(1),
  AIS_PROVIDER_TYPE: z.enum(['websocket', 'mock']).default('websocket'),
  AIS_PROVIDER_URL: z.string().default(''),
  AIS_PROVIDER_API_KEY: z.string().default(''),
  AIS_BOUNDING_BOXES: z.string().min(1),
  BACKEND_INTERNAL_URL: z.string().min(1),
  BACKEND_INTERNAL_TOKEN: z.string().min(1),
  WHITELIST_REFRESH_SECONDS: z.coerce.number().int().positive().default(300),
  MAX_MESSAGE_AGE_SECONDS: z.coerce.number().int().positive().default(300),
  BACKOFF_MIN_MS: z.coerce.number().int().positive().default(1000),
  BACKOFF_MAX_MS: z.coerce.number().int().positive().default(60000),
  DELIVERY_QUEUE_MAX: z.coerce.number().int().positive().default(1000),
  LOG_LEVEL: z.enum(['debug', 'info', 'warn', 'error']).default('info'),
})

export type WorkerConfig = z.infer<typeof configSchema>

export const loadConfig = (environment: NodeJS.ProcessEnv = process.env) => {
  const parsed = configSchema.parse(environment)

  if (parsed.AIS_PROVIDER_TYPE === 'websocket') {
    if (!parsed.AIS_PROVIDER_URL) {
      throw new Error(
        'AIS_PROVIDER_URL is required when AIS_PROVIDER_TYPE=websocket',
      )
    }
    if (!parsed.AIS_PROVIDER_API_KEY) {
      throw new Error(
        'AIS_PROVIDER_API_KEY is required when AIS_PROVIDER_TYPE=websocket',
      )
    }
  }

  return parsed
}
