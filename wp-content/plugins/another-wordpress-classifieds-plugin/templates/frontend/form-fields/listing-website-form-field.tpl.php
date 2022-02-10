<?php
/**
 * @package AWPCP\Templates\FormFields
 */

?><p class="awpcp-form-field awpcp-clearfix   awpcp-form-spacer">
    <?php $validator = $required ? 'required classifiedsurl' : 'classifiedsurl'; ?>
    <label class="awpcp-form-field__label" for="<?php echo esc_attr( $html['id'] ); ?>"><?php echo esc_html( $label ); ?><?php echo $required ? '<span class="required">*</span>' : ''; ?></label>
    <input class="awpcp-textfield awpcp-has-value inputbox <?php echo esc_attr( $validator ); ?>" id="<?php echo esc_attr( $html['id'] ); ?>" type="text" size="50" name="<?php echo esc_attr( $html['name'] ); ?>" value="<?php echo awpcp_esc_attr( $value ); // XSS Okay. ?>" />
    <?php echo awpcp_form_error( $html['name'], $errors ); // XSS Okay. ?>
</p>
