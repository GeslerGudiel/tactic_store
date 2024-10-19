<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/comercio_electronico/public/emprendedor/dashboard/js/scripts.js"></script>

<?php if ($message): ?>
    <script>
        Swal.fire({
            icon: "<?php echo $message['type']; ?>",
            title: "<?php echo ucfirst($message['type']); ?>",
            text: "<?php echo $message['text']; ?>"
        });
    </script>
<?php endif; ?>

</body>
</html>
