<?php
include 'includes/header.php';
include 'includes/db.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_msg = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            $success_msg = "Thank you! Your message has been sent. We will get back to you shortly.";

            // Auto-Responder
            $auto_subject = "We received your message: $subject";
            $auto_body = "Hi $name,\n\nThank you for contacting LM Hard Wine. We have received your inquiry and will get back to you as soon as possible.\n\nBest regards,\nThe LM Hard Wine Team\n\n---\nDisclaimer: If you did not send this message, please ignore this email. Someone may have entered your email address by mistake.";
            $headers = "From: no-reply@lmhardwine.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            // Send silently (don't show error to user if mail fails)
            @mail($email, $auto_subject, $auto_body, $headers);

        } catch (PDOException $e) {
            $error_msg = "Something went wrong. Please try again later.";
        }
    }
}
?>

<main style="padding-top: 100px; padding-bottom: var(--spacing-xl);">
    <div class="container">
        <div class="text-center fade-in" style="margin-bottom: var(--spacing-lg);">
            <h1 style="font-size: 3rem;">Contact Us</h1>
            <p style="color: var(--color-text-muted);">Taste the intensity. Get in touch.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-xl);">

            <!-- Contact Info -->
            <div class="fade-in">
                <h2 style="margin-bottom: 1.5rem;">Visit the Estate</h2>
                <div class="glass-card" style="margin-bottom: 2rem;">
                    <h3 style="color: var(--color-accent); margin-bottom: 0.5rem;">Location</h3>
                    <p style="color: #ccc; margin-bottom: 1.5rem;">
                        123 Vineyard Lane<br>
                        Volcanic Valley Region, CA 90210
                    </p>

                    <h3 style="color: var(--color-accent); margin-bottom: 0.5rem;">Opening Hours</h3>
                    <p style="color: #ccc;">
                        Mon - Fri: 10am - 6pm<br>
                        Sat - Sun: 11am - 8pm
                    </p>
                </div>

                <div class="glass-card">
                    <h3 style="color: var(--color-accent); margin-bottom: 0.5rem;">Direct Line</h3>
                    <p style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.5rem;">+1 (555) 123-4567</p>
                    <p style="color: #ccc;">info@lmhardwine.com</p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="glass-card fade-in" style="transition-delay: 0.2s;">
                <h2 style="margin-bottom: 1.5rem;">Send a Message</h2>

                <?php if ($success_msg): ?>
                    <div
                        style="background: rgba(76, 175, 80, 0.2); color: #4caf50; padding: 15px; border-radius: 4px; margin-bottom: 1.5rem; text-align: center; border: 1px solid rgba(76, 175, 80, 0.3);">
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div
                        style="background: rgba(244, 67, 54, 0.2); color: #f44336; padding: 15px; border-radius: 4px; margin-bottom: 1.5rem; text-align: center; border: 1px solid rgba(244, 67, 54, 0.3);">
                        <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; color: var(--color-text-muted);">Name</label>
                        <input type="text" name="name" class="form-control" required
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; color: var(--color-text-muted);">Email</label>
                        <input type="email" name="email" class="form-control" required
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; color: var(--color-text-muted);">Subject</label>
                        <select name="subject" class="form-control"
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px;">
                            <option value="Private Tasting" style="background: #141414;">Private Tasting</option>
                            <option value="Trade Inquiry" style="background: #141414;">Trade Inquiry</option>
                            <option value="General Question" style="background: #141414;">General Question</option>
                        </select>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; color: var(--color-text-muted);">Message</label>
                        <textarea name="message" rows="5" class="form-control" required
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; color: white; border-radius: 4px; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"
                        style="margin-top: 1rem; width: 100%; border: none;">Send Enquiry</button>
                </form>
            </div>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>