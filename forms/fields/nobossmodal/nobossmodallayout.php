<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');

$doc = JFactory::getDocument();

$modalLabel = $form->getAttribute('label');
$buttonConfirm = $form->getAttribute('buttonconfirm');
$buttonCancel = $form->getAttribute('buttoncancel');
$buttonReset = $form->getAttribute('buttonreset');
$description = $form->getAttribute('description');

// Texto para botão de salvar a modal
$buttonConfirmModal = empty($buttonConfirm) ? JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_BUTTON_CONFIRM_DEFAULT_LABEL") : JText::_($buttonConfirm);
$buttonCancelModal = empty($buttonCancel) ? JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_BUTTON_CANCEL_DEFAULT_LABEL") : JText::_($buttonCancel);
$buttonResetModal = empty($buttonReset) ? JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_BUTTON_RESET_DEFAULT_LABEL") : JText::_($buttonReset);
$firstFieldset = reset($fieldsets);

?>

<div data-id="modal" class="noboss-modal noboss-modal--tabs modal-wrapper fade in hidden" tabindex="-1" role="dialog" data-modal-name="<?php echo $form->getName(); ?>" >
	<div class="nb-modal-dialog">
		<div class="nb-modal-content">
			<?php if(!empty($modalLabel)){ ?>
				<div class="nb-modal-header">
					<h2><?php echo JText::_($modalLabel); ?></h2>
					<a href="#" data-id="button-cancel" class="btn btn-close buttons">×</a>
					<?php if(!empty($description)) { ?>
						<p><?php echo $description; ?></p>
					<?php } ?>
				</div>
			<?php } ?>
			<?php if(count($fieldsets) > 1) {?>
				<ul class="nav nav-tabs" data-id="modal-nav-tabs">
					<?php foreach ($fieldsets as $fieldKey => $fieldsetItem) { ?>
						<li class="<?php echo $fieldsetItem->label == $firstFieldset->label ? 'active' : ''; ?>">
							<a href="#<?php echo $fieldKey; ?>" data-id="modal-tab" data-toggle="tab"><?php echo empty($fieldsetItem->label) ? JText::_('LIB_NOBOSS_FIELD_NOBOSSMODAL_UNDEFINED_TAB_LABEL') : JText::_($fieldsetItem->label); ?></a>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>
			<div class="nb-modal-body" style="overflow-y: scroll;">
				<form name="nb-modal-form" data-id="nb-modal-form" >
					<?php foreach ($fieldsets as $fieldTabKey => $fieldsetTapPane){ ?>
						<div data-tab-id="<?php echo $fieldTabKey; ?>" class="fieldset modal-tab-pane <?php echo $fieldsetTapPane->label == $firstFieldset->label ? 'active' : ''; ?>" <?php echo isset($fieldsetTapPane->nbshowon) ? "data-nbshowon='{$fieldsetTapPane->nbshowon}'" : ""; ?> >
							<?php foreach ($form->getFieldset($fieldTabKey) as $field) {
									echo $field->renderField();
							} ?>
						</div>
					<?php } ?>
				</form>
			</div>
			<div class="nb-modal-footer">
				<a href="#" data-id="button-cancel" class="btn"><?php echo $buttonCancelModal; ?></a>
				<a href="#" data-id="button-confirm" class="btn btn-primary buttons"><?php echo $buttonConfirmModal; ?></a>
				<a href="#" data-id="button-reset" class="btn btn-reset"><?php echo $buttonResetModal; ?></a>
			</div>
		</div>
	</div>
</div>

<script>
	nobossmodal.loadedJsAndCss = <?php echo json_encode(array_merge(array_keys($doc->_styleSheets), array_keys($doc->_scripts))); ?>;
</script>
