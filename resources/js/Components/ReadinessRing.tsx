interface Props {
    score: number;
}

// Presentational-only threshold for how the ring is colored — doesn't touch
// GetReadinessScoreService or add a new business rule, purely how the
// existing 0-100 value is drawn.
function colorVar(score: number): string {
    if (score >= 70) {
        return 'var(--color-success)';
    }

    if (score >= 40) {
        return 'var(--color-warning)';
    }

    return 'var(--color-error)';
}

export default function ReadinessRing({ score }: Props) {
    const color = colorVar(score);

    return (
        <div
            className="relative flex h-28 w-28 shrink-0 items-center justify-center rounded-full"
            style={{ background: `conic-gradient(${color} ${score * 3.6}deg, var(--color-border) 0deg)` }}
        >
            <div className="flex h-20 w-20 items-center justify-center rounded-full bg-surface">
                <span className="text-2xl font-semibold text-text-primary">{score}%</span>
            </div>
        </div>
    );
}
