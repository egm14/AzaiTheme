
{if !isset($content_only) || !$content_only}
              </div><!-- #center_column -->
              {if isset($left_column_size) && !empty($left_column_size)}
                <div id="left_column" class="column col-xs-12 col-sm-{$left_column_size|intval}">{$HOOK_LEFT_COLUMN}</div>
              {/if}
              </div><!--.row-->
            </div><!--.large-left-->
            {if isset($right_column_size) && !empty($right_column_size)}
              <div id="right_column" class="col-xs-12 col-sm-{$right_column_size|intval} column">{$HOOK_RIGHT_COLUMN}</div>
            {/if}
            </div><!-- .row -->
          </div><!-- .container -->
        </div><!-- #columns -->
        {assign var='displayMegaHome' value={hook h='tmMegaLayoutHome'}}
        {if isset($HOOK_HOME) && $HOOK_HOME|trim}
          {if $displayMegaHome}
            {hook h='tmMegaLayoutHome'}
          {else}
            <div class="container">
              {$HOOK_HOME}
            </div>
          {/if}
        {/if}
      </div><!-- .columns-container -->
      {assign var='displayMegaFooter' value={hook h='tmMegaLayoutFooter'}}
      {if isset($HOOK_FOOTER) || $displayMegaFooter}
        <!-- Footer -->
        <div class="footer-container">
          <footer id="footer">
            {if $displayMegaFooter}
              {$displayMegaFooter}

              <div class="footerMobil">

              <div id="redes-desk" class="container" style="display:block;text-align: center;"> 	
              	 <a href="https://www.facebook.com/azaistore" target="_blank"><!--<img src="/img/azai-facebook.png" alt="Azai Facebook" width="40" />--><i class="fa fa-facebook" aria-hidden="true"></i></a>
              	 <a href="https://twitter.com/azaistore" target="_blank">
                  <!--<img src="/img/azai-twitter.png" alt="Azai Twitter" width="40" />-->
                  <i class="fa fa-twitter" aria-hidden="true"></i></a>
              	 <a href="https://www.instagram.com/azaistore/" target="_blank"><!--<img src="/img/azai-instagram.png" alt="Azai Instagram" width="40" />--><i class="fa fa-instagram" aria-hidden="true"></i></a>
              </div>
                	<!--<div id="redes-mob2" class="container" style="display:none;text-align: center;padding: 0px 70px;">
                		<div class="col-xs-4 col-sm-4 col-md-4"><a href="https://www.facebook.com/azaistore"><img src="/img/azai-facebook.png" alt="Azai Facebook" width="40" /></a></div>
                		<div class="col-xs-4 col-sm-4 col-md-4"><a href="https://twitter.com/azaistore"><img src="/img/azai-twitter.png" alt="Azai Twitter" width="40" /></a></div>
                		<div class="col-xs-4 col-sm-4 col-md-4"><a href="https://www.instagram.com/azaistore/"><img src="/img/azai-instagram.png" alt="Azai Instagram" width="40" /></a></div>
                	 </div>-->
             {if $lang_iso == 'en'}
              <div class="copyright">©CopyRight 2018 AZAI STORE. All Right Reserved</div>
              
            	{elseif $lang_iso == 'es'}
            	<div class="copyright">©CopyRight 2018 AZAI STORE. TODOS LOS DERECHOS RESERVADOS</div>
						   {/if}
             </div>
	
         {else}
              <div class="container">
                {$HOOK_FOOTER}
               
              </div>
            {/if}
          </footer>
           
        </div><!-- #footer -->
      {/if}

    </div><!-- #page -->
  {/if}

  {include file="$tpl_dir./global.tpl"}
  <!-- Custom javascript call after Slider Revolution - to no conflict -->
      <script type="text/javascript" src="{$js_dir}custom.js"></script>
      <!--<script type="text/javascript">
                /*================== SECTION PRODUCT VIEW - PINCH PAN =================*/
          // General Mobile Events
          if (window.innerWidth < 768) {
            console.log("pantalla menor de 768px");

            //Fancybox mobile "Pinch Pan"
            var elemet1 = ($('#image-block'))[0]; 
            var mc1 = new Hammer(elemet1);

            mc1.on("tap", function(ev){
              console.log("Se ha hecho: "+ ev.type);

              if($('.fancybox-opened').length < 0){
                console.log("No se ha creado el div.fancybox-opened");
                }else{
                  setTimeout(function(){
                    console.log("Añadiendo div after");
                    var zoomdiv = $('.fancybox-skin');
                  //zoomdiv.css("background-color","blue");
                    zoomdiv.after("<div class=" +"pinch-zoom"+">Pinch To Zoom</div>");
                  
                    //Desaparecer Pinch-zoom al tocar
                var element2 = ($('.fancybox-inner'))[0];
                var mc2 = new Hammer.Manager(element2);

                var pinch = new Hammer.Pinch();
                  var pan = new Hammer.Pan();

                  pinch.recognizeWith(pan);
                  mc2.add([pinch, pan]);
                

                mc2.on("pinchstart panstart", function (ev) {
                    console.log("Clase deshabilitada por: " + ev.type);
                    $('.pinch-zoom').addClass('disable');
                  });
                  },100);
                }
             console.log("img.fancybox-inner está aquí");
            });
          }

      </script>-->
  </body>
    
</html>