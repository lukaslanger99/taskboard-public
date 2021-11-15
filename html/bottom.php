        <div id="dynamic-form">
            <div class="bg-modal" id="bg-modal-dynamicform">
                <div class="modal-content" id="dynamic-modal-content"></div>
            </div>
        </div>
    </body>
</html>
<?php
    if ($_GET['success']) {
        echo '<script>printSuccessToast(\''.$_GET['success'].'\')</script>';
    }
    if ($_GET['error']) {
        echo '<script>printErrorToast(\''.$_GET['error'].'\')</script>';
    }
    if ($_GET['warning']) {
        echo '<script>printWarningToast(\''.$_GET['warning'].'\')</script>';
    }