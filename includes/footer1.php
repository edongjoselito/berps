 <div class="footer">

        <h1>Copyright &copy; 2020. All Rights Reserved. <span>Powered by <a href="http://www.softtechservices.net" target="_blank">SoftTech Solutions</a></span></h1>

        <div class="blocker"></div>

      </div>





      <div class="blocker"></div>

    </div>

  </div> <!-- end #footer -->

  

  <script src="js/jquery-1.9.1.min.js" type="text/javascript"></script>

  <script type="text/javascript">

      $(document).ready(function () {

          $(".wrap").hover(function () {

              $(this).find('.fadeHover').fadeIn();

          }, function () { 

              $(this).find('.fadeHover').fadeOut();

          });

      });

    </script>



    <script type="text/javascript">

                    $(function() {

      

                  $('.show').click(function() {

      	    $('.checkboxContent').slideToggle('fast');

      	    return false;

      	});

              });
              
           $('.menu').click(function() {
      	    $('#menu').slideToggle('fast');
      	    return false;
      	});    




      $(document).click(function() {

           $('.checkboxContent').slideUp('fast');

      });

      

      $(".checkboxContent").click(function(e) {

          e.stopPropagation(); });

   </script>



