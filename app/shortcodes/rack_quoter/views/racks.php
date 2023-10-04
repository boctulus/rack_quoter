<?php
  
  $cfg  = include __DIR__ . '/../config/config.php';

  $dims = $cfg['dims'];

?>

<div class="row">
  <div class="main-form">
    <!---->
    <ul class="list-step" >
      <li   class="active">
        <a aria-controls="step-01"  >Rack Dimensions</a>
      </li>
      <li>
        <a aria-controls="step-02"  >Decking Options</a>
      </li>
      <!---->
      <li>
        <a aria-controls="step-03"  >Space Availability</a>
      </li>
      <!---->
      <!---->
      <li   >
        <a aria-controls="step-04"  >Aisle Dimensions</a>
      </li>
      <!---->
      <!---->
      <!---->
    </ul>
    <!---->
    <div class="tab-content">

      <div id="step-01" class="active tab-pane clearfix">
        <div class="-item">
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span >Upright Height</span>: </label>
            <div class="col-sm-8 select-wrapper -secondary">
              <select class="form-control ">
                <?php foreach ($dims['h'] as $e): ?>
                  <option label="<?= $e ?>&quot;" value="<?= $e ?>"><?= $e ?>"</option>
                <?php endforeach; ?>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span >Upright Depth</span>: </label>
            <div class="col-sm-8 select-wrapper -secondary">
              <select class="form-control ">
                <?php foreach ($dims['d'] as $e): ?>
                  <option label="<?= $e ?>&quot;" value="<?= $e ?>"><?= $e ?>"</option>
                <?php endforeach; ?>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span >Beam Length</span>: </label>
            <div class="col-sm-8 select-wrapper">
              <select class="form-control ">
              <?php foreach ($dims['l'] as $e): ?>
                  <option label="<?= $e ?>&quot;" value="<?= $e ?>"><?= $e ?>" Long</option>
                <?php endforeach; ?>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span>Beam Levels: <br>
                <span style="font-size: small" >(not including floor)</span>
              </span>
            </label>
            <div class="col-sm-8">
              <div class="check-wrapper">
                <?php foreach (range(2,$dims['max_levels']) as $level): ?>
                <!---->
                <label class="check-default line">
                  <input type="radio" name="selected-level" value="<?= $level ?>"  class="ng-pristine ">
                  <span ><?= $level ?></span>
                </label>
                <!---->
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <div class="-item -img">
          <!---->
          <img src="<?= shortcode_asset(__DIR__ . '/img/NewPalletRack.png') ?>">
          <!---->
          <p class="-img-caption" >4 Beam Levels in diagram</p>
        </div>
      </div>

      <div id="step-02" class="tab-pane clearfix d-none">
        <div class="clearfix row">

          <!-- left half-tab -->
          <div class="-item-tab-half decking col-md-6">
            <div class="-caption" >
              <h4 >Do you want wire decking?</h4>
              <div class="check-wrapper">
                <label for="wireDeckingY" class="check-default">
                  <input type="radio" name="wireDecking" id="wireDeckingY"   class="ng-pristine " value="true">
                  <span >Yes</span>
                </label>
                <label for="wireDeckingN" class="check-default">
                  <input type="radio" name="wireDecking" id="wireDeckingN"   class="ng-pristine " value="false">
                  <span >No</span>
                </label>
              </div>
            </div>
            <div class="-img" >
              <img src="<?= shortcode_asset(__DIR__ . '/img/tab2-img01.png') ?>">
            </div>
          </div>

          <!-- right half-tab -->
          <div class="-item-tab-half pallets col-md-6">
            <div class="-caption"> 
              <h4 >Do you want pallet supports?</h4>
              <div class="radio-wrapper">
                <label for="palletSupportsY" class="check-default">
                  <input type="radio" name="palletSupports" id="palletSupportsY" class="ng-pristine " >
                  <span >Yes</span>
                </label>
                <label for="palletSupportsN" class="check-default">
                  <input type="radio" name="palletSupports" id="palletSupportsN" class="ng-pristine ">
                  <span >No</span>
                </label>
              </div>
            </div>
            <div class="-img">
              <img src="<?= shortcode_asset(__DIR__ . '/img/tab2-img02.png') ?>">
            </div>
          </div>

        </div>
      </div>

      <div id="step-03" class="clearfix tab-pane text-center d-none">
        <!---->
        <!---->
        <!---->
        <h4  >What is the length and width of the space where you want pallet rack?</h4>
        <!---->
        <div class="-img centered">
          <!---->
          <img src="<?= shortcode_asset(__DIR__ . '/img/multiple.png') ?>" >
          <!---->
          <!---->
          <!---->
        </div>
        <form role="form" name="areaForm" class="form-material ng-valid-maxlength ng-valid-required" style="margin-top: 50px;">
          <div class="clearfix">
            <div class="form-inline">
              <div class="form-group -custom validation-group">
                <label for="length" class="-primary" >Length</label>
                <input type="text" id="length" name="length" class="form-control ng-valid-maxlength ng-valid-required"  valid-number=""   placeholder="feet">
                <div class="text-right " >
                  <!---->
                </div>
              </div>
              <!---->
              <div  class="form-group -custom validation-group">
                <label for="width" class="-secondary" >Width</label>
                <input type="text" id="width" name="width" class="form-control ng-valid-maxlength ng-valid-required"  valid-number=""   placeholder="feet">
                <div class="text-right " >
                  <!---->
                </div>
              </div>
              <!---->
            </div>
          </div>
        </form>
      </div>

      <?php
        $params = "design=multiple-rows&condition=new&height=96&depth=42&beam_length=96&beam_levels=5&length=50&width=200&aisle=132&usesupport=false&usewiredeck=false";
      ?>

      <div id="step-04" class="clearfix tab-pane text-center d-none">
        <div class="clearfix text-center">
            <a href="/" class="navbar-brand">
              <img src="<?= shortcode_asset(__DIR__ . '/img/WES-Logo.png') ?>" alt="logo" style="max-height: 45px;">
            </a>
            <h2 style="font-size: 40px">Pallet Rack Layout Drawing</h2><!---->
            <p id="palletsCount" class="subheading">
                This Layout Will Store {{N}} Pallets</p><!---->
        </div>
        <div class="clearfix">
            <div class="row">
                <div class="col-md-12 col-xs-12 text-center">
                    <div class="-content">
                      
                        <div class="main-img">
                          <img src="/api/drawing/preview/?<?= $params ?>">
                        </div><!---->
                        <div><!---->
                            <p class="subheading">Redraw With Different Forklift</p><!---->
                            <div class="-content alignment" style="margin-top:-35px;"><span class="primary-description" style="font-style: italic;">Click Below to view your space
                                    with a different aisle dimension</span><!---->
                                <div aisle="controller.Model.Aisle">
                                    <div class="flex sb sm-c wrap aisle-wrapper">
                                        <div class="-item-inline">
                                            <div class="-caption"><label for="itemCheck-4" class="check-default -l"><input
                                                        type="radio" id="itemCheck-4" name="aisle" value="66"
                                                        class="ng-pristine "> <span>5' 6''
                                                        Aisle</span></label></div><!---->
                                            <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img04.png') ?>">
                                                <h4>Drexel Forklift</h4>
                                            </div><!---->
                                        </div>
                                        <div class="-item-inline">
                                            <div class="-caption"><label for="itemCheck-3" class="check-default -l"><input
                                                        type="radio" id="itemCheck-3" name="aisle" value="78"
                                                        class="ng-pristine "> <span>6' 6''
                                                        Aisle</span></label></div><!---->
                                            <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img03.png') ?> ">
                                                <h4>Bendi Forklift</h4>
                                            </div><!---->
                                        </div>
                                        <div class="-item-inline">
                                            <div class="-caption"><label for="itemCheck-2" class="check-default -l"><input
                                                        type="radio" id="itemCheck-2" name="aisle" value="114"
                                                        class="ng-pristine "> <span>9' 6''
                                                        Aisle</span></label></div><!---->
                                            <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img02.png') ?>">
                                                <h4>Reach Truck</h4>
                                            </div><!---->
                                        </div>
                                        <div class="-item-inline">
                                            <div class="-caption"><label for="itemCheck-6" class="check-default -l"><input
                                                        type="radio" id="itemCheck-6" name="aisle" value="132"
                                                        class="ng-pristine "> <span>11'
                                                        Aisle</span></label></div><!---->
                                            <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img03.png') ?>">
                                                <h4>3 wheel forklift</h4>
                                            </div><!---->
                                        </div>
                                        <div class="-item-inline">
                                            <div class="-caption"><label for="itemCheck-1" class="check-default -l"><input
                                                        type="radio" id="itemCheck-1" name="aisle" value="156" checked="checked"
                                                        class="ng-pristine "> <span>13'
                                                        Aisle</span></label></div><!---->
                                            <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img01.png') ?>">
                                                <h4>4 wheel forklift</h4>
                                            </div><!---->
                                        </div>
                                    </div>
                                    <div class="clearfix aisle-wrapper">
                                        <div class="-item-inline">
                                            <div class="form-group text-center"><label for="itemCheck-5"
                                                    class="check-default -l"><input type="radio" id="itemCheck-5"
                                                        name="aisle" data-ng-checked="customeAisle"> <span
                                                        class="h4">Enter a custom aisle
                                                        dimension</span></label> 
                                                        <input type="text" id="custom-aisle-dim" class="form-control -small" style="margin: auto; margin-top: 15px; display:none;" placeholder="inches">
                                            </div>
                                        </div>
                                    </div>
                                </div><!----><!---->
                            </div>
                        </div><!---->
                    </div>
                </div>
            </div>
        </div>



      </div><!-- end step -->



      <!---->
    </div>
  </div>
  <div class="main-form-btn-group">
    <button id="tabPrev" class="btn btn-primary -prev no-animate d-none">
      <i class="fa fa-angle-left"></i>
      <span >Back</span>
    </button>
    <button id="tabNext" class="btn btn-primary -next"  >
      <span>Next</span>
      <!-- span class="d-none">View Drawing</span -->
      <i class="fa fa-angle-right"></i>
    </button>
  </div>
  <!---->
</div>

<script>
let model = {}

model.wireDecking    = false
model.palletSupports = false

const prev             =  jQuery('#tabPrev');
const next             =  jQuery('#tabNext');
const decking          =  jQuery('.-item-tab-half.decking')
const pallets          =  jQuery('.-item-tab-half.pallets')

const wireDeckingY     =  jQuery("label[for='wireDeckingY']");
const wireDeckingN     =  jQuery("label[for='wireDeckingN']");
const palletSupportsY  =  jQuery("label[for='palletSupportsY']");
const palletSupportsN  =  jQuery("label[for='palletSupportsN']");


const disable = (selector) => {
   jQuery(selector).prop('disabled', true);
}

const enable = (selector) => {
   jQuery(selector).prop('disabled', false);
}

const show = (selector) => {
   jQuery(selector).removeClass('d-none').prop('disabled', false);
}

const hide = (selector) => {
   jQuery(selector).addClass('d-none');
}

const visibilize = (selector, state) => {
  state = (state === true || state === 1 || state == 'visibile') 
   jQuery(selector).css('visibility', state ? 'visible' : 'hidden');
}

const showPrevBtn = () => {
  show(prev);
}

const hidePrevBtn = () => {
  hide(prev);
}

const getCurrentStep = () => {
  return jQuery('.list-step li.active').index() + 1;
}

const hideStep = (num) => {
   jQuery(`#step-0${num}`).remove('active').addClass('d-none');
}

const showStep = (num) => {
   jQuery(`#step-0${num}`).addClass('active').removeClass('d-none');
}

const move2Step = (num) => {
  hideStep(getCurrentStep())
  showStep(num)
}

document.addEventListener("DOMContentLoaded", function() {
  const steps =  jQuery('.list-step li').length;

  const updateNavigationButtons = () => {
    const currentStep =  jQuery('.list-step li.active').index() + 1;

    if (currentStep === 1) {
      disable(prev);
      show(next);
    } else if (currentStep < steps) {
      show(prev);
      show(next);
    } else {
      show(prev);
      hide(next);
    }
  };

  const updateContent = () => {
    const currentStep =  jQuery('.list-step li.active').index() + 1;
    move2Step(currentStep);
  }

  // Inicializar el primer paso como activo
   jQuery('.list-step li:first').addClass('active');
   jQuery('.tab-pane:first').addClass('active');

  show(prev);
  disable(prev);

  // Manejar el evento de clic en los elementos de la lista de pasos
   jQuery('.list-step li').click(function() {
     jQuery('.list-step li').removeClass('active');
     jQuery(this).addClass('active');

    const target =  jQuery(this).find('a').attr('href');
     jQuery('.tab-pane').removeClass('active');
     jQuery(target).addClass('active');

    updateNavigationButtons();
    updateContent();
  });

  // Manejar el evento de clic en los botones de navegación
   jQuery('#tabPrev').click(function() {
    const activeStep =  jQuery('.list-step li.active');
    const prevStep = activeStep.prev();

    if (prevStep.length > 0) {
      activeStep.removeClass('active');
      prevStep.addClass('active');

      const target = prevStep.find('a').attr('href');
       jQuery('.tab-pane').removeClass('active');
       jQuery(target).addClass('active');

      updateNavigationButtons();
      updateContent();
    }
  });

   jQuery('#tabNext').click(function() {
    const activeStep =  jQuery('.list-step li.active');
    const nextStep = activeStep.next();

    if (nextStep.length > 0) {
      activeStep.removeClass('active');
      nextStep.addClass('active');

      const target = nextStep.find('a').attr('href');
       jQuery('.tab-pane').removeClass('active');
       jQuery(target).addClass('active');

      updateNavigationButtons();
      updateContent();
    }
  });

  jQuery("input[name='aisle']").change(function() {
      const el = jQuery(this)
      
      if (el.attr('id') == 'itemCheck-5'){
        jQuery('#custom-aisle-dim').show()
      } else {
        jQuery('#custom-aisle-dim').hide()
      }
  });

  const wireDeckingHandler = () => {
    model.wireDecking = wireDeckingY.find('input').prop('checked')
    visibilize(pallets, !model.wireDecking)
  }

  wireDeckingY.find('input').change(function() {
    wireDeckingHandler()
  });

  wireDeckingN.find('input').change(function() {
    wireDeckingHandler()
  });


  const palletsHandler = () => {
    model.palletSupports = palletSupportsY.find('input').prop('checked')
    visibilize(decking, !model.palletSupports)
  }

  palletSupportsY.find('input').change(function() {
    palletsHandler()
  });

  palletSupportsN.find('input').change(function() {
    palletsHandler()
  });
  


  move2Step(4); ///////
});

</script>