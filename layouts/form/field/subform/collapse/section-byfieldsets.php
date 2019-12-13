<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Layouts
 * @version			1.0
 * @author			No Boss Technology <contato@noboss.com.br>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);
?>

<div
	class="subform-repeatable-group subform-repeatable-group-<?php echo $unique_subform_id; ?> shrink grow"
	data-base-name="<?php echo $basegroup; ?>"
	data-group="<?php echo $group; ?>"
>
    <div class="noboss-collapse" data-noboss-collapse>
        <div class="noboss-collapse--wrapper">
            <span class="noboss-collapse__icon rotate-icon material-icon">keyboard_arrow_up</span>
            <p class="noboss-collapse__title"></p>
        </div>
    </div>
    <?php if (!empty($buttons)) : ?>
	<div class="btn-toolbar text-right">
		<div class="btn-group">
        <?php if (!empty($buttons['remove'])) : ?>
                <a data-bt="remove-item" class="btn btn-mini button btn-danger group-remove-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_REMOVE'); ?>">
                    <span class="icon-minus" aria-hidden="true"></span>
                </a>
            <?php endif; ?>
            <?php if (!empty($buttons['add'])) : ?>
				<a class="btn btn-mini button btn-success group-add-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_ADD'); ?>">
					<span class="icon-plus" aria-hidden="true"></span>
				</a>
			<?php endif; ?>
			<?php if (!empty($buttons['move'])) : ?>
				<a class="btn btn-mini button btn-primary group-move-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_MOVE'); ?>">
					<span class="icon-move" aria-hidden="true"></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="row-fluid">
<?php foreach ($form->getFieldsets() as $fieldset) : ?>
<fieldset class="<?php if (!empty($fieldset->class)){ echo $fieldset->class; } ?>">
	<?php if (!empty($fieldset->label)) : ?>
	<legend><?php echo JText::_($fieldset->label); ?></legend>
	<?php endif; ?>
<?php foreach ($form->getFieldset($fieldset->name) as $field) : ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
</fieldset>
<?php endforeach; ?>
	</div>
</div>
