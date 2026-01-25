<footer
    style="background-color: var(--color-bg-secondary); padding: var(--spacing-lg) 0; margin-top: var(--spacing-xl);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: var(--spacing-md);">
            <div style="flex: 1; min-width: 250px;">
                <a href="index.php" class="logo" style="font-size: 2rem; margin-bottom: 1rem; display: block;">LM
                    <span>HARD</span> WINE</a>
                <p style="color: var(--color-text-muted); max-width: 300px;">
                    Curating the finest, hardest wines for the most discerning palates. Experience the bold taste of
                    excellence.
                </p>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <h4 style="color: var(--color-accent);">Explore</h4>
                <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <li><a href="products.php?type=Red">Red Wines</a></li>
                    <li><a href="products.php?type=White">White Wines</a></li>
                    <li><a href="products.php">Limited Editions</a></li>
                    <li><a href="products.php">Gift Sets</a></li>
                </ul>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <h4 style="color: var(--color-accent);">Contact</h4>
                <p style="color: var(--color-text-muted);">
                    123 Vineyard Lane<br>
                    Wine Valley, CA 90210<br>
                    <br>
                    info@lmhardwine.com
                </p>
            </div>
        </div>

        <div
            style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: var(--spacing-md); padding-top: var(--spacing-sm); text-align: center; color: var(--color-text-muted); font-size: 0.8rem;">
            &copy;
            <?php echo date("Y"); ?> LM Hard Wine. All rights reserved. Please drink responsibly.
        </div>
    </div>
</footer>

<script src="js/app.js?v=1.1"></script>
</body>

</html>