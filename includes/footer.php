</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div><h3>Sweet Crumbs</h3><p>Online cake shopping with cakes, cupcakes, puddings, cookies and cheesecakes.</p></div>
        <div><h4>Customer</h4><a href="<?= e(app_url('auth/profile.php')) ?>">Profile</a><a href="<?= e(app_url('orders/orders.php')) ?>">My orders</a></div>
        <div><h4>Contact Us</h4><p>Phone: 07123456789</p><p>Email: sweetcrumbs@email.com</p></div>
    </div>
    <div class="footer-bottom">&copy; <?= date('Y') ?> Sweet Crumbs</div>
</footer>
<script src="<?= e(app_url('assets/js/app.js')) ?>"></script>
</body>
</html>
