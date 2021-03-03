
    </div>
    </main>

    <br />
    <footer class="footer mt-auto pl-3 py-1">
        <div class="container">
            Copyright &copy; <?=date('Y'), PHP_EOL?>
        </div>
    </footer>

    <script src="<?=$site_url?>/js/jquery-3.4.1.min.js" type="text/javascript"></script>
    <script src="<?=$site_url?>/js/popper.min.js" type="text/javascript"></script>
    <script src="<?=$site_url?>/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?=$site_url?>/js/ajaxnav.js" type="text/javascript"></script>
    <script src="<?=$site_url?>/js/langs/<?=$l['lang']?>.js" type="text/javascript"></script>
    <?=$this->putJsUrls()?>
    <?=$this->putJsFiles()?>
</body>
</html>