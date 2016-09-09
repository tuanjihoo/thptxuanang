

<!-- Footer -->

<?php

if (td_util::get_option('tds_footer') != 'no') {

    td_api_footer_template::_helper_show_footer();

}

?>





<!-- Sub Footer -->

<?php if (td_util::get_option('tds_sub_footer') != 'no') { ?>

    <div class="td-sub-footer-container">

        <div class="td-container">

            <div class="td-pb-row">

                <div class="td-pb-span7 td-sub-footer-menu">

                        <?php

                        wp_nav_menu(array(

                            'theme_location' => 'footer-menu',

                            'menu_class'=> 'td-subfooter-menu',

                            'fallback_cb' => 'td_wp_footer_menu'

                        ));



                        //if no menu

                        function td_wp_footer_menu() {

                            //do nothing?

                        }

                        ?>

                </div>



                <div class="td-pb-span5 td-sub-footer-copy">

                    <?php

                    $tds_footer_copyright = td_util::get_option('tds_footer_copyright');

                    $tds_footer_copy_symbol = td_util::get_option('tds_footer_copy_symbol');



                    //show copyright symbol

                    if ($tds_footer_copy_symbol == '') {

                        echo '&copy; ';

                    }



                    echo $tds_footer_copyright;

                    ?>

                </div>

            </div>

        </div>

    </div>

<?php } ?>

    </div><!--close content div-->

</div><!--close td-outer-wrap-->



<?php wp_footer(); ?>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-62654776-2', 'auto');
  ga('send', 'pageview');

</script>


</body>

</html>