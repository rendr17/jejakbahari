export class ExponentialBackoff {
  private attempt = 0

  constructor(
    private readonly minMs: number = 1000,
    private readonly maxMs: number = 60000,
  ) {}

  nextDelay(): number {
    const base = Math.min(this.minMs * 2 ** this.attempt, this.maxMs)
    const jitter = Math.random() * base * 0.25
    const delay = Math.round(base + jitter)
    this.attempt++
    return delay
  }

  reset(): void {
    this.attempt = 0
  }

  get currentAttempt(): number {
    return this.attempt
  }

  async sleep(): Promise<void> {
    const delay = this.nextDelay()
    await new Promise((resolve) => setTimeout(resolve, delay))
  }
}
