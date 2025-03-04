  <footer class="site-footer">
    <p>&copy; <span id="year"></span> Eli Larson</p>
    <p class="last-updated">Last updated: <?php echo date("F j, Y", filemtime(__FILE__)); ?></p>
  </footer>

  <!-- Lightbox Preview Container -->
  <div id="lightbox" class="lightbox">
    <span class="close">&times;</span>
    <span class="arrow prev">&#10094;</span>
    <img id="lightbox-img" src="" alt="Preview">
    <span class="arrow next">&#10095;</span>
  </div>

  <!-- Script for image lazy loading and performance -->
  <script src="js/script.js"></script>
</body>
</html> 