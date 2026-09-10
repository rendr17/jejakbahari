import { z } from 'zod'

const configSchema = z.object({
  NODE_ENV: z
    .enum(['development', 'test', 'production'])
    .default('development'),
  WORKER_ID: z.string().min(1),
  AIS_PROVIDER_URL: z.url(),
  AIS_PROVIDER_API_KEY: z.string().min(1),
  AIS_BOUNDING_BOXES: z.string().min(1),
  BACKEND_INTERNAL_URL: z.url(),
  BACKEND_INTERNAL_TOKEN: z.string().min(1),
  WHITELIST_REFRESH_SECONDS: z.coerce.number().int().positive().default(300),
  MAX_MESSAGE_AGE_SECONDS: z.coerce.number().int().positive().default(300),
  BACKOFF_MIN_MS: z.coerce.number().int().positive().default(1000),
  BACKOFF_MAX_MS: z.coerce.number().int().positive().default(60000),
  DELIVERY_QUEUE_MAX: z.coerce.number().int().positive().default(1000),
  LOG_LEVEL: z.enum(['debug', 'info', 'warn', 'error']).default('info'),
})

export const loadConfig = (environment: NodeJS.ProcessEnv = process.env) =>
  configSchema.parse(environment)
