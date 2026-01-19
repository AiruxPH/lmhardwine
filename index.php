<?php include 'includes/header.php'; ?>

<main>
    <!-- Hero Section -->
    <section class="hero" style="
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        background: radial-gradient(circle at center, #2a050b 0%, #0a0a0a 70%);
        position: relative;
        overflow: hidden;
    ">
        <!-- Abstract Background Element -->
        <div style="
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, rgba(114, 14, 30, 0.2) 0%, transparent 70%);
            filter: blur(80px);
            z-index: 0;
        "></div>

        <div class="container" style="position: relative; z-index: 1;">
            <p class="fade-in text-accent"
                style="text-transform: uppercase; letter-spacing: 4px; margin-bottom: 1rem; font-size: 0.9rem;">Premium
                Selection</p>
            <h1 class="fade-in"
                style="font-size: clamp(3rem, 8vw, 6rem); margin-bottom: 2rem; text-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                UNLEASH THE <br>
                <span style="color: var(--color-primary); -webkit-text-stroke: 1px rgba(255,255,255,0.1);">BOLD</span>
                TASTE
            </h1>
            <p class="fade-in"
                style="font-size: 1.25rem; color: var(--color-text-muted); max-width: 600px; margin: 0 auto 3rem;">
                Crafted for those who demand intensity. LM Hard Wine delivers an uncompromising experience in every
                bottle.
            </p>
            <div class="fade-in">
                <a href="#collection" class="btn btn-primary" style="margin-right: 1rem;">Explore Collection</a>
                <a href="#about" class="btn">Our Philosophy</a>
            </div>
        </div>
    </section>

    <!-- Collection Preview -->
    <section id="collection" style="padding: var(--spacing-xl) 0;">
        <div class="container">
            <div class="text-center animate-on-scroll"
                style="margin-bottom: var(--spacing-lg); opacity: 0; transform: translateY(20px); transition: 1s ease;">
                <h2 style="margin-bottom: 0.5rem;">The Hard Collection</h2>
                <div style="width: 60px; height: 3px; background: var(--color-primary); margin: 0 auto;"></div>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-md);">
                <!-- Product Card 1 -->
                <div class="glass-card animate-on-scroll"
                    style="opacity: 0; transform: translateY(20px); transition: 1s ease; transition-delay: 0.1s;">
                    <div
                        style="height: 300px; background: #1a1a1a; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px; position: relative; overflow: hidden;">
                        <span style="font-size: 4rem; opacity: 0.1; font-weight: 700;">RED</span>
                        <!-- Placeholder specific style -->
                        <div
                            style="position: absolute; inset: 0; background: linear-gradient(45deg, rgba(114, 14, 30, 0.1), transparent);">
                        </div>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="font-size: 1.5rem; margin-bottom: 0.25rem;">Crimson Impact</h3>
                            <p style="color: var(--color-accent); font-size: 0.9rem;">Cabernet Sauvignon • 2024</p>
                        </div>
                        <span style="font-size: 1.25rem; font-weight: 600;">$89</span>
                    </div>
                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        A full-bodied giant with notes of dark cherry, leather, and smoked oak. Not for the faint of
                        heart.
                    </p>
                    <a href="#" class="btn" style="width: 100%; text-align: center;">Add to Cellar</a>
                </div>

                <!-- Product Card 2 -->
                <div class="glass-card animate-on-scroll"
                    style="opacity: 0; transform: translateY(20px); transition: 1s ease; transition-delay: 0.2s;">
                    <div
                        style="height: 300px; background: #1a1a1a; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px; position: relative; overflow: hidden;">
                        <span style="font-size: 4rem; opacity: 0.1; font-weight: 700;">ONYX</span>
                        <div
                            style="position: absolute; inset: 0; background: linear-gradient(45deg, rgba(80, 80, 80, 0.1), transparent);">
                        </div>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="font-size: 1.5rem; margin-bottom: 0.25rem;">Midnight Reserve</h3>
                            <p style="color: var(--color-accent); font-size: 0.9rem;">Syrah Blend • 2022</p>
                        </div>
                        <span style="font-size: 1.25rem; font-weight: 600;">$120</span>
                    </div>
                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        Velvety texture meets intense spice. Aged in charred barrels for a finish that lingers forever.
                    </p>
                    <a href="#" class="btn" style="width: 100%; text-align: center;">Add to Cellar</a>
                </div>

                <!-- Product Card 3 -->
                <div class="glass-card animate-on-scroll"
                    style="opacity: 0; transform: translateY(20px); transition: 1s ease; transition-delay: 0.3s;">
                    <div
                        style="height: 300px; background: #1a1a1a; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px; position: relative; overflow: hidden;">
                        <span style="font-size: 4rem; opacity: 0.1; font-weight: 700;">GOLD</span>
                        <div
                            style="position: absolute; inset: 0; background: linear-gradient(45deg, rgba(212, 175, 55, 0.1), transparent);">
                        </div>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="font-size: 1.5rem; margin-bottom: 0.25rem;">Liquid Gold</h3>
                            <p style="color: var(--color-accent); font-size: 0.9rem;">Chardonnay • 2023</p>
                        </div>
                        <span style="font-size: 1.25rem; font-weight: 600;">$95</span>
                    </div>
                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        Unexpectedly crisp with a steel backbone. Notes of granite, lemon zest, and white flowers.
                    </p>
                    <a href="#" class="btn" style="width: 100%; text-align: center;">Add to Cellar</a>
                </div>
            </div>

            <div style="text-align: center; margin-top: var(--spacing-lg);">
                <a href="#" class="btn btn-primary">View Full Catalogue</a>
            </div>
        </div>
    </section>

    <!-- Banner Section -->
    <section
        style="background: url('assets/vineyard-bg.jpg') no-repeat center center/cover; position: relative; padding: var(--spacing-xl) 0;">
        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.8);"></div>
        <div class="container animate-on-scroll"
            style="position: relative; z-index: 1; text-align: center; max-width: 800px; opacity: 0; transform: translateY(20px); transition: 1s ease;">
            <h2 style="font-size: 3rem; margin-bottom: 1.5rem;">Heritage of Hardness</h2>
            <p style="font-size: 1.1rem; color: #ccc; margin-bottom: 2rem;">
                Our vines grow in the toughest volcanic soils, struggling for every drop of water.
                This struggle produces fruit of concentrated power and unparalleled depth.
                We don't make easy wine. We make Hard Wine.
            </p>
            <a href="#about" class="btn">Read Our Story</a>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>