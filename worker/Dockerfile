FROM node:22-slim

WORKDIR /app

COPY package.json pnpm-lock.yaml ./
RUN corepack enable pnpm && pnpm install --frozen-lockfile --prod

COPY tsconfig.json ./
COPY src/ ./src/
RUN pnpm run build

CMD ["node", "dist/index.js"]
