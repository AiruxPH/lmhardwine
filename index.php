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
                style="margin-bottom: var(--spacing-lg); ">
                <h2 style="margin-bottom: 0.5rem;">The Hard Collection</h2>
                <div style="width: 60px; height: 3px; background: var(--color-primary); margin: 0 auto;"></div>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-md);">
                <!-- Product Card 1 -->
                <div class="glass-card animate-on-scroll"
                    style=" transition-delay: 0.1s;">
                    <a href="product-details.php?id=1" style="text-decoration: none; color: inherit;">
                        <div
                            style="height: 300px; background: #1a1a1a; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px; position: relative; overflow: hidden;">
                            <span style="font-size: 4rem; opacity: 0.1; font-weight: 700;">RED</span>
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
                    </a>
                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        A full-bodied giant with notes of dark cherry, leather, and smoked oak. Not for the faint of
                        heart.
                    </p>
                    <a href="product-details.php?id=1" class="btn" style="width: 100%; text-align: center;">View
                        Details</a>
                </div>

                <!-- Product Card 2 -->
                <div class="glass-card animate-on-scroll"
                    style=" transition-delay: 0.2s;">
                    <a href="product-details.php?id=2" style="text-decoration: none; color: inherit;">
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
                    </a>
                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        Velvety texture meets intense spice. Aged in charred barrels for a finish that lingers forever.
                    </p>
                    <a href="product-details.php?id=2" class="btn" style="width: 100%; text-align: center;">View
                        Details</a>
                </div>

                <!-- Product Card 3 -->
                <div class="glass-card animate-on-scroll"
                    style=" transition-delay: 0.3s;">
                    <a href="product-details.php?id=3" style="text-decoration: none; color: inherit;">
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
                    </a>
                    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        Unexpectedly crisp with a steel backbone. Notes of granite, lemon zest, and white flowers.
                    </p>
                    <a href="product-details.php?id=3" class="btn" style="width: 100%; text-align: center;">View
                        Details</a>
                </div>
            </div>

            <div style="text-align: center; margin-top: var(--spacing-lg);">
                <a href="#" class="btn btn-primary">View Full Catalogue</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" style="padding: var(--spacing-xl) 0; background: #0f0f0f;">
        <div class="container">
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-xl); align-items: center;">
                <div class="animate-on-scroll" style="">
                    <div
                        style="width: 100%; height: 400px; background: #1a1a1a; border-radius: 8px; position: relative; overflow: hidden;">
                        <!-- Placeholder for About Image -->
                        <div
                            style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(30,30,30,0.8), rgba(0,0,0,0.9)); display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 5rem; opacity: 0.05; font-weight: 700;">STORY</span>
                        </div>
                    </div>
                </div>

                <div class="animate-on-scroll"
                    style=" transition-delay: 0.2s;">
                    <p class="text-accent"
                        style="text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 1rem;">
                        Our Heritage</p>
                    <h2 style="margin-bottom: 1.5rem;">Forged in Fire & Stone</h2>
                    <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">
                        LM Hard Wine isn't born in a gentle valley. It fights for existence on the scorched slopes of
                        ancient volcanoes. The soil is unforgiving—rocky, mineral-rich, and hard.
                    </p>
                    <p style="color: var(--color-text-muted); margin-bottom: 2rem;">
                        This struggle forces our vines to drive their roots deep into the earth, resulting in grapes of
                        extraordinary concentration and complexity. We don't interfere. We simply bottle the intensity.
                    </p>

                    <div style="display: flex; gap: 2rem;">
                        <div>
                            <h3 style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0;">1885</h3>
                            <span style="font-size: 0.9rem; color: var(--color-text-muted);">Est. Date</span>
                        </div>
                        <div>
                            <h3 style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0;">2,400ft</h3>
                            <span style="font-size: 0.9rem; color: var(--color-text-muted);">Elevation</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" style="padding: var(--spacing-xl) 0; position: relative; overflow: hidden;">
        <!-- Background Decor -->
        <div
            style="position: absolute; top: 0; right: 0; width: 40vw; height: 40vw; background: radial-gradient(circle, rgba(114, 14, 30, 0.1) 0%, transparent 70%); filter: blur(60px); opacity: 0.5;">
        </div>

        <div class="container" style="position: relative; z-index: 1;">
            <div class="text-center" style="margin-bottom: var(--spacing-lg);">
                <h2>Taste the Intensity</h2>
                <p style="color: var(--color-text-muted);">Book a private tasting or inquire about trade partnerships.
                </p>
            </div>

            <div class="glass-card animate-on-scroll"
                style="max-width: 800px; margin: 0 auto; ">
                <form action="#" method="POST"
                    style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div style="grid-column: 1 / -1; display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; color: var(--color-text-muted);">Name</label>
                        <input type="text"
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px; font-family: inherit;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; color: var(--color-text-muted);">Email</label>
                        <input type="email"
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px; font-family: inherit;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; color: var(--color-text-muted);">Subject</label>
                        <select
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px; font-family: inherit;">
                            <option style="background: #141414;">Private Tasting</option>
                            <option style="background: #141414;">Trade Inquiry</option>
                            <option style="background: #141414;">General Question</option>
                        </select>
                    </div>

                    <div style="grid-column: 1 / -1; display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; color: var(--color-text-muted);">Message</label>
                        <textarea rows="4"
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px; font-family: inherit; resize: vertical;"></textarea>
                    </div>

                    <div style="grid-column: 1 / -1; margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; border: none;">Send
                            Enquiry</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
