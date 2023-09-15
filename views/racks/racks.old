<div class="row">
  <div class="main-form">
    <!---->
    <ul class="list-step" data-ng-if="!hideMobile">
      <li data-ng-class="{'active' : controller.selectedStep === controller.steps.ItemStep}" data-ng-click="controller.selectedStep = controller.steps.ItemStep" class="active" style="">
        <a aria-controls="step-01" data-toggle="tab" lang-bind="controller.fields.common.RackDimensions">Rack Dimensions</a>
      </li>
      <li data-ng-class="{'active' : controller.selectedStep === controller.steps.DeckingStep}" data-ng-click="controller.selectedStep = controller.steps.DeckingStep" class="" style="">
        <a aria-controls="step-02" data-toggle="tab" lang-bind="controller.fields.common.DeckingOptions">Decking Options</a>
      </li>
      <!---->
      <li data-ng-class="{'active' : controller.selectedStep === controller.steps.AreaStep}" data-ng-click="controller.selectedStep = controller.steps.AreaStep" data-ng-if="controller.wireDecks || controller.hasSupports" class="" style="">
        <a aria-controls="step-03" data-toggle="tab" lang-bind="controller.fields.common.SpaceAvailability">Space Availability</a>
      </li>
      <!---->
      <!---->
      <li data-ng-if="controller.isAisleAvailable" data-ng-class="{'active' : controller.selectedStep === controller.steps.AisleStep}" data-ng-click="!areaForm.$invalid &amp;&amp; (controller.selectedStep = controller.steps.AisleStep)">
        <a aria-controls="step-04" data-toggle="tab" lang-bind="controller.fields.common.AisleDimensions">Aisle Dimensions</a>
      </li>
      <!---->
      <!---->
      <!---->
    </ul>
    <!---->
    <div class="tab-content">
      <div id="step-01" class="active tab-pane clearfix" data-ng-show="controller.selectedStep === controller.steps.ItemStep" style="">
        <div class="-item">
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span lang-bind="controller.fields.palletrack.UprightHeight">Upright Height</span>: </label>
            <div class="col-sm-8 select-wrapper -secondary">
              <select data-ng-options="height as height.Name for height in controller.heights" data-ng-model="controller.Model.SelectedHeight" class="form-control ng-pristine ng-untouched ng-valid ng-not-empty" style="">
                <option label="96&quot;" value="object:244">96"</option>
                <option label="120&quot;" value="object:245">120"</option>
                <option label="144&quot;" value="object:243" selected="selected">144"</option>
                <option label="192&quot;" value="object:246">192"</option>
                <option label="240&quot;" value="object:247">240"</option>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span lang-bind="controller.fields.palletrack.UprightDeep">Upright Depth</span>: </label>
            <div class="col-sm-8 select-wrapper -secondary">
              <select data-ng-options="deep as deep.Name for deep in controller.Model.SelectedHeight.Deeps" data-ng-model="controller.Model.SelectedDeep" class="form-control ng-pristine ng-untouched ng-valid ng-not-empty" style="">
                <option label="36&quot;" value="object:248" selected="selected">36"</option>
                <option label="42&quot;" value="object:249">42"</option>
                <option label="48&quot;" value="object:250">48"</option>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span lang-bind="controller.fields.palletrack.BeamLength">Beam Length</span>: </label>
            <div class="col-sm-8 select-wrapper">
              <select data-ng-options="beam as beam.Name for beam in controller.Model.SelectedDeep.Beams" data-ng-model="controller.Model.SelectedBeam" class="form-control ng-pristine ng-untouched ng-valid ng-not-empty" style="">
                <option label="96&quot; Long" value="object:251" selected="selected">96" Long</option>
                <option label="108&quot; Long" value="object:252">108" Long</option>
                <option label="120&quot; Long" value="object:253">120" Long</option>
                <option label="144&quot; Long" value="object:254">144" Long</option>
              </select>
              <i class="fa fa-angle-down"></i>
            </div>
          </div>
          <div class="form-group clearfix">
            <label class="control-label col-sm-4">
              <span>Beam Levels: <br>
                <span style="font-size: small" lang-bind="controller.fields.palletrack.NotIncludingFloor">(not including floor)</span>
              </span>
            </label>
            <div class="col-sm-8">
              <div class="check-wrapper">
                <!---->
                <label class="check-default line" data-ng-repeat="i in controller.range(controller.MinLevels, controller.MaxLevels + 1, 1)" style="">
                  <input type="radio" name="selected-level" value="2" data-ng-model="controller.Model.Levels" class="ng-pristine ng-untouched ng-valid ng-not-empty">
                  <span data-ng-bind="::i">2</span>
                </label>
                <!---->
                <label class="check-default line" data-ng-repeat="i in controller.range(controller.MinLevels, controller.MaxLevels + 1, 1)">
                  <input type="radio" name="selected-level" value="3" data-ng-model="controller.Model.Levels" class="ng-pristine ng-untouched ng-valid ng-not-empty">
                  <span data-ng-bind="::i">3</span>
                </label>
                <!---->
                <label class="check-default line" data-ng-repeat="i in controller.range(controller.MinLevels, controller.MaxLevels + 1, 1)">
                  <input type="radio" name="selected-level" value="4" data-ng-model="controller.Model.Levels" class="ng-pristine ng-untouched ng-valid ng-not-empty">
                  <span data-ng-bind="::i">4</span>
                </label>
                <!---->
                <label class="check-default line" data-ng-repeat="i in controller.range(controller.MinLevels, controller.MaxLevels + 1, 1)">
                  <input type="radio" name="selected-level" value="5" data-ng-model="controller.Model.Levels" class="ng-pristine ng-untouched ng-valid ng-not-empty">
                  <span data-ng-bind="::i">5</span>
                </label>
                <!---->
                <label class="check-default line" data-ng-repeat="i in controller.range(controller.MinLevels, controller.MaxLevels + 1, 1)">
                  <input type="radio" name="selected-level" value="6" data-ng-model="controller.Model.Levels" class="ng-pristine ng-untouched ng-valid ng-not-empty">
                  <span data-ng-bind="::i">6</span>
                </label>
                <!---->
              </div>
            </div>
          </div>
        </div>
        <div class="-item -img">
          <!---->
          <img src="<?= asset('images/NewPalletRack.png') ?>" alt="" data-ng-if="!!controller.Model.SelectedItem" class="" style="">
          <!---->
          <p class="-img-caption" lang-bind="controller.fields.palletrack.MasterImgTitle">4 Beam Levels in diagram</p>
        </div>
      </div>
      <div id="step-02" class="active tab-pane clearfix d-none" data-ng-show="controller.selectedStep === controller.steps.DeckingStep" style="">
        <div class="clearfix row">
          <div class="-item-tab-half col-md-6">
            <div class="-caption" data-ng-show="!controller.Model.UseSupport">
              <h4 lang-bind="controller.fields.palletrack.WantWireDeck">Do you want wire decking?</h4>
              <div class="check-wrapper">
                <label for="wireDeckingY" class="check-default">
                  <input type="radio" name="wireDecking" id="wireDeckingY" data-ng-model="controller.Model.UseWireDeck" data-ng-value="true" class="ng-pristine ng-untouched ng-valid ng-not-empty" value="true" style="">
                  <span lang-bind="controller.fields.common.Yes">Yes</span>
                </label>
                <label for="wireDeckingN" class="check-default">
                  <input type="radio" name="wireDecking" id="wireDeckingN" data-ng-model="controller.Model.UseWireDeck" data-ng-value="false" class="ng-pristine ng-untouched ng-valid ng-not-empty" value="false" style="">
                  <span lang-bind="controller.fields.common.No">No</span>
                </label>
              </div>
            </div>
            <div class="-img" data-ng-show="!controller.Model.UseSupport">
              <img src="<?= asset('images/tab2-img01.png') ?>" alt="">
            </div>
          </div>
          <div class="-item-tab-half col-md-6">
            <div class="-caption d-none" data-ng-show="!controller.Model.UseWireDeck" style="">
              <h4 lang-bind="controller.fields.palletrack.WantSupport">Do you want pallet supports?</h4>
              <div class="radio-wrapper">
                <label for="palletSupportsY" class="check-default">
                  <input type="radio" name="palletSupports" id="palletSupportsY" data-ng-model="controller.Model.UseSupport" data-ng-value="true" class="ng-pristine ng-untouched ng-valid ng-not-empty" value="true" style="">
                  <span lang-bind="controller.fields.common.Yes">Yes</span>
                </label>
                <label for="palletSupportsN" class="check-default">
                  <input type="radio" name="palletSupports" id="palletSupportsN" data-ng-model="controller.Model.UseSupport" data-ng-value="false" class="ng-pristine ng-untouched ng-valid ng-not-empty" value="false" style="">
                  <span lang-bind="controller.fields.common.No">No</span>
                </label>
              </div>
            </div>
            <div class="-img d-none" data-ng-show="!controller.Model.UseWireDeck" style="">
              <img src="<?= asset('images/tab2-img02.png') ?>" alt="">
            </div>
          </div>
        </div>
      </div>
      <div id="step-03" class="active clearfix tab-pane text-center d-none" data-ng-show="controller.selectedStep === controller.steps.AreaStep" style="">
        <!---->
        <!---->
        <!---->
        <h4 lang-bind="controller.fields.palletrack.AreaTitleMulti" data-ng-if="controller.designTypes.multiple.Name === controller._design">What is the length and width of the space where you want pallet rack?</h4>
        <!---->
        <div class="-img centered">
          <!---->
          <img src="<?= asset('images/multiple.png') ?>" alt="" data-ng-if="controller.designTypes.multiple.Name === controller._design">
          <!---->
          <!---->
          <!---->
        </div>
        <form role="form" name="areaForm" class="form-material ng-pristine ng-valid-maxlength ng-valid ng-valid-required" style="margin-top: 50px;">
          <div class="clearfix">
            <div class="form-inline">
              <div class="form-group -custom validation-group">
                <label for="length" class="-primary" lang-bind="controller.fields.common.Length">Length</label>
                <input type="text" id="length" name="length" class="form-control ng-pristine ng-untouched ng-valid-maxlength ng-not-empty ng-valid ng-valid-required" data-ng-model="controller.Model.Length" required="" data-ng-maxlength="3" valid-number="" lang-bind="controller.fields.common.Feet" lang-bind-attr="placeholder" placeholder="feet" style="">
                <div class="text-right ng-inactive" data-ng-messages="areaForm.length.$error">
                  <!---->
                </div>
              </div>
              <!---->
              <div data-ng-if="controller.isAisleAvailable" class="form-group -custom validation-group">
                <label for="width" class="-secondary" lang-bind="controller.fields.common.Width">Width</label>
                <input type="text" id="width" name="width" class="form-control ng-pristine ng-untouched ng-valid-maxlength ng-not-empty ng-valid ng-valid-required" data-ng-model="controller.Model.Width" required="" data-ng-maxlength="3" valid-number="" lang-bind="controller.fields.common.Feet" lang-bind-attr="placeholder" placeholder="feet" style="">
                <div class="text-right ng-inactive" data-ng-messages="areaForm.width.$error">
                  <!---->
                </div>
              </div>
              <!---->
            </div>
          </div>
        </form>
      </div>
      <!---->
    </div>
  </div>
  <div class="main-form-btn-group">
    <button id="tabPrev" class="btn btn-primary -prev no-animate d-none" data-ng-click="controller.backStep()" data-ng-show="controller.selectedStep > 0" style="">
      <i class="fa fa-angle-left"></i>
      <span lang-bind="controller.fields.common.Back">Back</span>
    </button>
    <button id="tabNext" class="btn btn-primary -next" data-ng-click="controller.nextStep()" data-ng-disabled="controller.selectedStep === controller.steps.AreaStep &amp;&amp; areaForm.$invalid">
      <span lang-bind="controller.fields.common.Next" data-ng-show="!controller.isLastStep()" class="">Next</span>
      <span lang-bind="controller.fields.common.ViewDrawing" data-ng-show="controller.isLastStep()" class="d-none">View Drawing</span>
      <i class="fa fa-angle-right"></i>
    </button>
  </div>
  <!---->
</div>