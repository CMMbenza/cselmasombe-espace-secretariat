<?php
  // Sécurité: s'assure que la variable existe
  $annee_scolaire = $annee_scolaire ?? '2025 - 2026';
  $current_year   = date('Y');
?>
<!-- partial:partials/_footer.php -->
<footer class="footer border-top py-3 mt-4" role="contentinfo">
  <div class="container-fluid">
    <div class="d-sm-flex justify-content-between align-items-center gap-2">
      <span class="text-muted">
        Année scolaire
        <span class="badge bg-light text-muted border">
          <?= htmlspecialchars($annee_scolaire) ?>
        </span>
        — &copy; <?= $current_year ?> Complexe Scolaire ELMA.
        Thème propulsé par
        <a class="link-muted" href="https://www.bootstrapdash.com/" target="_blank" rel="noopener">BootstrapDash</a>.
        Tous droits réservés.
      </span>

      <span class="text-muted text-sm-end">
        Hand-crafted &amp; made with
        <i class="mdi mdi-heart text-danger" aria-hidden="true"></i>
      </span>
    </div>
  </div>

  <!-- Back to top -->
  <button type="button" class="btn btn-outline-secondary position-fixed" id="btnBackTop"
          style="right:1rem; bottom:1rem; display:none;" aria-label="Remonter en haut">
    <i class="mdi mdi-arrow-up"></i>
  </button>
</footer>
<!-- partial -->

</div><!-- main-panel ends -->
</div><!-- page-body-wrapper ends -->
</div><!-- container-scroller -->

<!-- ===== JS: Vendors (si des plugins jQuery sont utilisés) ===== -->
<script src="../../assets/vendors/js/vendor.bundle.base.js"></script>

<!-- ===== Plugins JS (charge seulement ce que tu utilises sur la page) ===== -->
<script src="../../assets/vendors/chart.js/chart.umd.js"></script>
<script src="../../assets/vendors/progressbar.js/progressbar.min.js"></script>
<script src="../../assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
<script src="../../assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<script src="../../assets/vendors/owl-carousel-2/owl.carousel.min.js"></script>
<script src="../../assets/js/jquery.cookie.js" type="text/javascript"></script>

<!-- ===== Bootstrap 5 Bundle (Popper inclus) — mets-le ici pour éviter les conflits ===== -->
<!-- Si vendor.bundle.base.js inclut DÉJÀ Bootstrap 5, commente la ligne ci-dessous -->
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<!-- ===== App core ===== -->
<script src="../../assets/js/off-canvas.js"></script>
<script src="../../assets/js/misc.js"></script>
<script src="../../assets/js/settings.js"></script>
<script src="../../assets/js/todolist.js"></script>
<script src="../../assets/js/proBanner.js"></script>
<script src="../../assets/js/dashboard.js"></script>

<!-- ===== Utils: Recherche table + Back to top ===== -->
<script>
  // Debounce générique
  function debounce(fn, delay) {
    let t; return function () {
      clearTimeout(t);
      const args = arguments, ctx = this;
      t = setTimeout(() => fn.apply(ctx, args), delay);
    };
  }

  /**
   * Filtre une table par texte.
   * @param {string} inputId - id de l'input
   * @param {string} tableId - id de la table
   * @param {number} colIndex - index de la colonne à filtrer (par défaut 1)
   */
  function filterTableByInput(inputId, tableId, colIndex = 1) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    const filter = (input.value || '').toString().trim().toUpperCase();
    const rows = table.tBodies.length ? table.tBodies[0].rows : table.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
      const td = rows[i].getElementsByTagName('td')[colIndex];
      if (!td) continue;
      const txt = (td.textContent || td.innerText || '').toUpperCase();
      rows[i].style.display = txt.indexOf(filter) > -1 ? '' : 'none';
    }
  }

  // Compat: ta fonction existante (si tu l'appelles ailleurs)
  function myFunctionSearch() {
    filterTableByInput('noms', 'myTable', 1);
  }

  // Bind auto si l'input #noms existe
  const _bindSearch = () => {
    const input = document.getElementById('noms');
    if (input) {
      input.addEventListener('input', debounce(() => filterTableByInput('noms', 'myTable', 1), 150));
    }
  };

  // Back to top
  (function () {
    const btn = document.getElementById('btnBackTop');
    if (!btn) return;
    const toggle = () => { btn.style.display = (window.scrollY > 200) ? 'inline-flex' : 'none'; };
    window.addEventListener('scroll', toggle, { passive: true });
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    toggle();
  })();

  // DOM ready (avec Bootstrap différé)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', _bindSearch);
  } else {
    _bindSearch();
  }
</script>

</body>
</html>
