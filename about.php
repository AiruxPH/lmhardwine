<?php include 'includes/header.php'; ?>

<main style="padding-top: 100px; padding-bottom: var(--spacing-xl);">
    <div class="container">
        <!-- Header -->
        <div class="text-center fade-in" style="margin-bottom: var(--spacing-lg);">
            <h1 style="font-size: 3rem;">Our Story</h1>
            <p style="color: var(--color-text-muted);">The legacy behind the label.</p>
        </div>

        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-xl); align-items: center;">
            <div class="animate-on-scroll">
                <div
                    style="width: 100%; height: 500px; background: #1a1a1a; border-radius: 8px; position: relative; overflow: hidden;">
                    <!-- Placeholder for About Image -->
                    <div
                        style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(30,30,30,0.8), rgba(0,0,0,0.9)); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 5rem; opacity: 0.05; font-weight: 700;">HISTORY</span>
                    </div>
                </div>
            </div>

            <div class="animate-on-scroll" style="transition-delay: 0.2s;">
                <p class="text-accent"
                    style="text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 1rem;">
                    Since 1885</p>
                <h2 style="margin-bottom: 1.5rem;">Forged in Fire & Stone</h2>
                <p style="color: var(--color-text-muted); margin-bottom: 1.5rem; line-height: 1.8;">
                    LM Hard Wine isn't born in a gentle valley. It fights for existence on the scorched slopes of
                    ancient volcanoes. The soil is unforgiving—rocky, mineral-rich, and hard. This struggle forces our
                    vines to drive their roots deep into the earth, resulting in grapes of extraordinary concentration
                    and complexity.
                </p>
                <p style="color: var(--color-text-muted); margin-bottom: 1.5rem; line-height: 1.8;">
                    Our philosophy is simple: interference minimizes character. We let the land speak. From the
                    sun-drenched days to the freezing high-altitude nights, every extreme condition is captured in the
                    bottle.
                </p>
                <p style="color: var(--color-text-muted); margin-bottom: 2rem; line-height: 1.8;">
                    Today, we collaborate with select independent growers who share our vision of uncompromising quality
                    ("Hard Wine").
                    Together, we bring you a collection that challenges the palate and ignites the senses.
                </p>

                <div style="display: flex; gap: 3rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 2rem;">
                    <div>
                        <h3 style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0;">1885</h3>
                        <span style="font-size: 0.9rem; color: var(--color-text-muted);">Est. Date</span>
                    </div>
                    <div>
                        <h3 style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0;">2,400ft</h3>
                        <span style="font-size: 0.9rem; color: var(--color-text-muted);">Elevation</span>
                    </div>
                    <div>
                        <h3 style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0;">15+</h3>
                        <span style="font-size: 0.9rem; color: var(--color-text-muted);">Partner Vineyards</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>