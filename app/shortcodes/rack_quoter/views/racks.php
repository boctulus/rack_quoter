<?php

use boctulus\SW\core\libs\Url;

$cfg  = include __DIR__ . '/../config/config.php';

$dims = $cfg['dims'];

?>

<script>
  const base_url = '<?= Url::getBaseUrl() ?>'

  const endpoint_calc      = base_url + '/api/drawing/calc'
  const endpoint_draw      = base_url + '/api/drawing/preview'
  const endpoint_datasheet = base_url + '/api/drawing/datasheet'
  const verb = 'GET'
  const dataType = 'json' // pudo ser "json"
  const contentType = 'application/json'

  const ajax_error_alert = {
    title: "Error",
    text: "Hubo un error. Intente más tarde.",
    icon: "warning", // "warning", "error", "success" and "info"
  }

  let state = {
    aisle: 66,
    usewiredeck: null,
    usesupport: null,
    height: null,
    depth: null,
    length: null,
    width: null,
    beam_length: null,
    beam_levels: null,

    /*
      Propiedades computadas las inicializo en false
    */

    pallet_qty: false,
    completed_step: false,
  }; 

  const updateState = (new_state) => {
    for (const key in new_state) {
      if (new_state.hasOwnProperty(key)) {
          state[key] = new_state[key];
      }
    }

    calc_draw();

    return state;
  }
 
  const getParams = (as_string = false) => {
    if (as_string){
      return getQueryString(state);
    } 

    return state;
  }

  /*
    Calc and draw 
  */
  const calc_draw = () => {
    if (nonEmptyValues(getParams()) === false){
      return;
    }

    params = getParams(true);

    updateImage(params);
    updateDataSheet(params);

    getPalletQty(endpoint_calc, params, verb, dataType, contentType)
    .then(palletQty => {
      jQuery('#lb_pallet_qty').text(palletQty)
    })
    .catch(error => {
      console.error(error.message);
    });
  }

  document.addEventListener("DOMContentLoaded", function() {
   

    height      = jQuery('#sel-height').val();
    depth       = jQuery('#sel-depth').val();
    beam_length = jQuery('#sel-beam_length').val();

    updateState({
      height,
      depth,
      beam_length
    });

    document.querySelectorAll("input[type='radio'][name='selected-level']").forEach(function(el) {
        el.addEventListener('change', function() {
            const level   = parseInt(el.dataset.level);

            if (state.completed_step === false || state.completed_step == '') {
                updateState({completed_step: 1});
            }

            updateState({beam_levels: level});

            let params = getParams();

            params.completed_step = state.completed_step;
            params.current_step = 1;

            mergeQueryParamsIntoHistoryAPI(params);
        });
    });

    document.getElementById('btn-set-aisle').addEventListener('click', function(e) {
      const aisle = document.querySelector('input#custom-aisle-dim').value;

      if (aisle !== ''){
        updateState({aisle});
      }
    });

    document.querySelectorAll("input[type='radio'][name='aisle']").forEach(function(el) {
      el.addEventListener('click', function() {
          const aisle   = el.dataset.value;
          const checked = el.checked;

          if (checked) {
            updateState({aisle});
          }

        });
    });

    document.getElementById('sel-height').addEventListener('click', function(e) {
      updateState({height: this.value});
    });

    document.getElementById('sel-depth').addEventListener('click', function(e) {
      updateState({depth: this.value});
    });

    document.getElementById('sel-beam_length').addEventListener('click', function(e) {
      updateState({beam_length: this.value});
    });

    document.getElementById('length').addEventListener('change', function(e) {
      updateState({length: this.value});
    });

    document.getElementById('width').addEventListener('change', function(e) {
      updateState({width: this.value});
    });


  });



  // Limpio valor de aisle si selecciona de la lista
  jQuery("input[type='radio'][name='aisle']").click(() => {
    jQuery('input#custom-aisle-dim').val('')
  });

  const getImageWidth = () => {
    return document.querySelector('.main-img').offsetWidth;
  }

  const updateImage = (params) => {
    const img_src = endpoint_draw + '?' + params + '&img_w=' + getImageWidth();

    console.log('img src', img_src);

    jQuery('#rendered-img').attr('src', img_src);
  }

  const updateDataSheet = (params) => {
    const img_src = endpoint_datasheet + '?' + params;

    console.log('img src', img_src);

    jQuery('#rendered-datasheet').attr('src', img_src);
  }

  const getPalletQty = async (endpoint_calc, params, verb, dataType, contentType) => {
    const url = endpoint_calc + '?' + params;

    console.log(url)

    try {
      const res = await jQuery.ajax({
        url: url,
        type: verb,
        dataType,
        contentType,
        cache: false
      });

      return res.data.pallets;
    } catch (error) {
      console.log('Error:', error);
      throw new Error('Error al obtener la cantidad de pallets');
    }
  };

</script>


<div class="row">
  <div class="main-form">
    <!---->
    <ul class="list-step">
      <li class="active">
        <a aria-controls="step-01">Rack Dimensions</a>
      </li>
      <li>
        <a aria-controls="step-02">Decking Options</a>
      </li>
      <!---->
      <li>
        <a aria-controls="step-03">Space Av.</a>
      </li>
      <!---->
      <!---->
      <li>
        <a aria-controls="step-04">Aisle Dimensions</a>
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
              <span>Upright Height</span>: </label>
            <div class="col-sm-8 select-wrapper -secondary">
              <select id="sel-height" class="form-control">
                <?php foreach ($dims['h'] as $e) : ?>
                  <option label="<?= $e ?>&quot;" value="<?= $e ?>"><?= $e ?>"</option>
                <?php endforeach; ?>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span>Upright Depth</span>: </label>
            <div class="col-sm-8 select-wrapper -secondary">
              <select id="sel-depth" class="form-control ">
                <?php foreach ($dims['d'] as $e) : ?>
                  <option label="<?= $e ?>&quot;" value="<?= $e ?>"><?= $e ?>"</option>
                <?php endforeach; ?>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span>Beam Length</span>: </label>
            <div class="col-sm-8 select-wrapper">
              <select id="sel-beam_length" class="form-control ">
                <?php foreach ($dims['l'] as $e) : ?>
                  <option label="<?= $e ?>&quot;" value="<?= $e ?>"><?= $e ?>" Long</option>
                <?php endforeach; ?>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span>Beam Levels: <br>
                <span style="font-size: small">(not including floor)</span>
              </span>
            </label>
            <div class="col-sm-8">
              <div class="check-wrapper">
                <?php foreach (range(2, $dims['max_levels']) as $level) : ?>
                  <!---->
                  <label class="check-default line" style="margin-right: 0px; margin-left: 3px;">
                    <input type="radio" name="selected-level" data-level="<?= $level ?>" class="ng-pristine ">
                    <span><?= $level ?></span>
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
          <p class="-img-caption">4 Beam Levels in diagram</p>
        </div>
      </div>

      <div id="step-02" class="tab-pane clearfix d-none">
        <div class="clearfix row">

          <!-- left half-tab -->
          <div class="-item-tab-half decking col-md-6">
            <div class="-caption">
              <h4>Do you want wire decking?</h4>
              <div class="check-wrapper">
                <label for="wireDeckingY" class="check-default">
                  <input type="radio" name="wireDecking" id="wireDeckingY" class="ng-pristine " value="true">
                  <span>Yes</span>
                </label>
                <label for="wireDeckingN" class="check-default">
                  <input type="radio" name="wireDecking" id="wireDeckingN" class="ng-pristine " value="false">
                  <span>No</span>
                </label>
              </div>
            </div>
            <div class="-img">
              <img src="<?= shortcode_asset(__DIR__ . '/img/tab2-img01.png') ?>">
            </div>
          </div>

          <!-- right half-tab -->
          <div class="-item-tab-half pallets col-md-6">
            <div class="-caption">
              <h4>Do you want pallet supports?</h4>
              <div class="radio-wrapper">
                <label for="palletSupportsY" class="check-default">
                  <input type="radio" name="palletSupports" id="palletSupportsY" class="ng-pristine ">
                  <span>Yes</span>
                </label>
                <label for="palletSupportsN" class="check-default">
                  <input type="radio" name="palletSupports" id="palletSupportsN" class="ng-pristine ">
                  <span>No</span>
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
        <h4>What is the length and width of the space where you want pallet rack?</h4>
        <!---->
        <div class="-img centered">
          <!---->
          <img src="<?= shortcode_asset(__DIR__ . '/img/multiple.png') ?>">
          <!---->
          <!---->
          <!---->
        </div>
        <form role="form" name="areaForm" class="form-material ng-valid-maxlength ng-valid-required" style="margin-top: 50px;">
          <div class="clearfix">
            <div class="form-inline">
              <div class="form-group -custom validation-group">
                <label for="length" class="-primary">Length</label>
                <input type="number" id="length" name="length" class="form-control ng-valid-maxlength ng-valid-required" valid-number="" placeholder="feet">
                <div class="text-right ">
                  <!---->
                </div>
              </div>
              <!---->
              <div class="form-group -custom validation-group">
                <label for="width" class="-secondary">Width</label>
                <input type="number" id="width" name="width" class="form-control ng-valid-maxlength ng-valid-required" valid-number="" placeholder="feet">
                <div class="text-right ">
                  <!---->
                </div>
              </div>
              <!---->
            </div>
          </div>
        </form>
      </div>

      <div id="step-04" class="clearfix tab-pane text-center d-none">
        <div class="clearfix text-center">
          <a href="/" class="navbar-brand">
            <img src="<?= shortcode_asset(__DIR__ . '/img/WES-Logo.png') ?>" alt="logo" style="max-height: 45px;">
          </a>
          <h2 style="font-size: 40px">Pallet Rack Layout Drawing</h2><!---->
          <p id="palletsCount" class="subheading">
            This Layout Will Store <span id="lb_pallet_qty"></span> Pallets</p><!---->
        </div>
        <div class="clearfix">
          <div class="row">
            <div class="col-md-12 col-xs-12 text-center">
              <div class="-content">

                <div class="main-img">
                  <img src="<?= asset('img/1x1-00ff007f.png') ?>" id="rendered-img">
                  <br>
                  <img src="<?= shortcode_asset(__DIR__ . '/img/datasheet_blank.jpeg') ?>" style="margin-top: -10px" id="rendered-datasheet">
                </div><!---->
                <div><!---->
                  <p class="subheading">Redraw With Different Forklift</p><!---->
                  <div class="-content alignment" style="margin-top:-35px;"><span class="primary-description" style="font-style: italic;">Click Below to view your space
                      with a different aisle dimension</span><!---->
                    <div aisle="controller.Model.Aisle">
                      <div class="flex sb sm-c wrap aisle-wrapper">
                        <div class="-item-inline">
                          <div class="-caption"><label for="itemCheck-4" class="check-default -l"><input type="radio" id="itemCheck-4" name="aisle" data-value="66" class="ng-pristine" checked="checked"> <span>5' 6''
                                Aisle</span></label></div><!---->
                          <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img04.png') ?>">
                            <h4>Drexel Forklift</h4>
                          </div><!---->
                        </div>
                        <div class="-item-inline">
                          <div class="-caption"><label for="itemCheck-3" class="check-default -l"><input type="radio" id="itemCheck-3" name="aisle" data-value="78" class="ng-pristine "> <span>6' 6''
                                Aisle</span></label></div><!---->
                          <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img03.png') ?> ">
                            <h4>Bendi Forklift</h4>
                          </div><!---->
                        </div>
                        <div class="-item-inline">
                          <div class="-caption"><label for="itemCheck-2" class="check-default -l"><input type="radio" id="itemCheck-2" name="aisle" data-value="114" class="ng-pristine "> <span>9' 6''
                                Aisle</span></label></div><!---->
                          <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img02.png') ?>">
                            <h4>Reach Truck</h4>
                          </div><!---->
                        </div>
                        <div class="-item-inline">
                          <div class="-caption"><label for="itemCheck-6" class="check-default -l"><input type="radio" id="itemCheck-6" name="aisle" data-value="132" class="ng-pristine "> <span>11'
                                Aisle</span></label></div><!---->
                          <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img03.png') ?>">
                            <h4>3 wheel forklift</h4>
                          </div><!---->
                        </div>
                        <div class="-item-inline">
                          <div class="-caption"><label for="itemCheck-1" class="check-default -l"><input type="radio" id="itemCheck-1" name="aisle" data-value="156" class="ng-pristine "> <span>13'
                                Aisle</span></label></div><!---->
                          <div class="-img"><img src="<?= shortcode_asset(__DIR__ . '/img/tab4-img01.png') ?>">
                            <h4>4 wheel forklift</h4>
                          </div><!---->
                        </div>
                      </div>

                  
                      <div class="row -item-inline" style="margin-top:15px;">
                        <div class="form-group text-center">
                          <label for="itemCheck-5" class="check-default -l">
                          <input type="radio" id="itemCheck-5" name="aisle" data-ng-checked="customeAisle"> 
                          <span class="h4">Enter a custom aisle dimension</span></label>
                        </div>

                        <div class="input-group col-lg-2 col-md-2 col-sm-3 col-xs-4 text-center" style="margin: auto; margin-top: 15px; margin-bottom: 10px; display: none;">
                          <input type="number" class="form-control" id="custom-aisle-dim" placeholder="inches" aria-label="inches">
                          <span class="input-group-addon btn" id="btn-set-aisle">OK</span>
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
      <span>Back</span>
    </button>
    <button id="tabNext" class="btn btn-primary -next">
      <span>Next</span>
      <!-- span class="d-none">View Drawing</span -->
      <i class="fa fa-angle-right"></i>
    </button>
  </div>
  <!---->
</div>

<script>

  const prev            = jQuery('#tabPrev');
  const next            = jQuery('#tabNext');
  const decking         = jQuery('.-item-tab-half.decking')
  const pallets         = jQuery('.-item-tab-half.pallets')

  const wireDeckingY    = jQuery("label[for='wireDeckingY']");
  const wireDeckingN    = jQuery("label[for='wireDeckingN']");
  const palletSupportsY = jQuery("label[for='palletSupportsY']");
  const palletSupportsN = jQuery("label[for='palletSupportsN']");


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

  const getcurrent_step = () => {
    return jQuery('.list-step li.active').index() + 1;
  }

  const hideStep = (num) => {
    jQuery(`#step-0${num}`).remove('active').addClass('d-none');
  }

  const showStep = (num) => {
    jQuery(`#step-0${num}`).addClass('active').removeClass('d-none');
  }

  const move2Step = (num) => {
    hideStep(getcurrent_step())
    showStep(num)
  }

  document.addEventListener("DOMContentLoaded", function() {
    const steps = jQuery('.list-step li').length;

    const updateNavigationButtons = () => {
      const current_step = jQuery('.list-step li.active').index() + 1;
      const prev_step    = current_step;

      if (nonEmptyValues(getParams()) === false){
          return;
      }

      if (current_step === 1) {
        disable(prev);
        show(next);
      } else if (current_step < steps) {
        show(prev); // ??
        show(next);
      } else {
        show(prev);
        hide(next);
      }

      mergeQueryParamsIntoHistoryAPI({
        current_step
      })

      if (current_step === 4) {
        calc_draw();
      }
    };

    const updateContent = () => {
      const current_step = jQuery('.list-step li.active').index() + 1;
      move2Step(current_step);
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

      const target = jQuery(this).find('a').attr('href');
      jQuery('.tab-pane').removeClass('active');
      jQuery(target).addClass('active');

      updateNavigationButtons();
      updateContent();
    });

    // Manejar el evento de clic en los botones de navegación
    jQuery('#tabPrev').click(function() {
      const activeStep = jQuery('.list-step li.active');
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
      const activeStep = jQuery('.list-step li.active');
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

      if (el.attr('id') == 'itemCheck-5') {
        jQuery('#custom-aisle-dim').parent().show()
      } else {
        jQuery('#custom-aisle-dim').parent().hide()
      }
    });

    const wireDeckingHandler = () => {
      updateState({usewiredeck: wireDeckingY.find('input').prop('checked')});

      if (state.usesupport === null){
        updateState({usesupport: false});
      }

      visibilize(pallets, !state.usewiredeck)
    }

    wireDeckingY.find('input').change(function() {
      wireDeckingHandler()
    });

    wireDeckingN.find('input').change(function() {
      wireDeckingHandler()
    });

    const palletsHandler = () => {
      updateState({usesupport: palletSupportsY.find('input').prop('checked')});

      if (state.usewiredeck === null){
        updateState({usewiredeck: false});
      }

      visibilize(decking, !state.usesupport)
    }

    palletSupportsY.find('input').change(function() {
      palletsHandler()
    });

    palletSupportsN.find('input').change(function() {
      palletsHandler()
    });



    // move2Step(4); ///////
  });
</script>