<?php
/*!
 * Base module for Birthday of the form DD/MM
 */

add_action( 'wpcf7_init', 'wpcf7_add_shortcode_birthday' );

function wpcf7_add_shortcode_birthday() {
  wpcf7_add_shortcode(
    array( 'birthday', 'birthday*' ),
    'wpcf7_birthday_shortcode_handler',
    true
  );
}

function wpcf7_birthday_shortcode_handler( $tag ) {
  $tag = new WPCF7_Shortcode( $tag );

  if ( empty( $tag->name ) )
    return '';

  $validation_error = wpcf7_get_validation_error( $tag->name );
        $class = wpcf7_form_controls_class( $tag->type, 'wpcf7-birthday' );

        if ( $validation_error )
                $class .= ' wpcf7-not-valid';

        $atts = array();

        $atts['size'] = $tag->get_size_option( '5' );
        $atts['maxlength'] = $tag->get_maxlength_option();
        $atts['class'] = $tag->get_class_option( $class );
        $atts['id'] = $tag->get_id_option();
        $atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

        if ( $tag->has_option( 'readonly' ) )
                $atts['readonly'] = 'readonly';

        if ( $tag->is_required() )
                $atts['aria-required'] = 'true';

        $atts['aria-invalid'] = $validation_error ? 'true' : 'false';

        $value = (string) reset( $tag->values );

        if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
                $atts['placeholder'] = $value;
                $value = '';
        } elseif ( '' === $value ) {
                $value = $tag->get_default_option();
        }

        $value = wpcf7_get_hangover( $tag->name, $value );

        $atts['value'] = $value;
        $atts['type'] = 'text';
        $atts['name'] = $tag->name;

        $atts = wpcf7_format_atts( $atts );

        $html = sprintf(
                '<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
                sanitize_html_class( $tag->name ), $atts, $validation_error );

        return $html;
}

add_filter( 'wpcf7_validate_birthday', 'wpcf7_birthday_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_birthday*', 'wpcf7_birthday_validation_filter', 10, 2 );

function wpcf7_birthday_validation_filter( $result, $tag ) {

        $tag = new WPCF7_Shortcode( $tag );

        $name = $tag->name;

  $value = isset( $_POST[$name] )
    ? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
                : '';

  if ( 'birthday' == $tag->type && $value != '' ) {
    if (preg_match('@^(0?[1-9]|[12][0-9]|3[01])/(0?[1-9]|1[0-2])$@', $value) != 1) {
      $result['valid'] = false;
      $result['reason'][$name] = wpcf7_get_message( 'invalid_birthday' );
    }
  }

  if ( 'birthday*' == $tag->type ) {
    if ($value == '') {
      $result['valid'] = false;
      $result['reason'][$name] = wpcf7_get_message( 'invalid_required' );
    } else if (preg_match('@^(0?[1-9]|[12][0-9]|3[01])/(0?[1-9]|1[0-2])$@', $value) != 1) {
      $result['valid'] = false;
      $result['reason'][$name] = wpcf7_get_message( 'invalid_birthday' );
    }
  }

        if ( isset( $result['reason'][$name] ) && $id = $tag->get_id_option() ) {
                $result['idref'][$name] = $id;
        }

  return $result;
}

add_filter( 'wpcf7_messages', 'wpcf7_birthday_messages' );

function wpcf7_birthday_messages( $messages ) {
  return array_merge( $messages, array(
    'invalid_birthday' => array(
      'description' => __( "Invalid Birthday Format.", 'contact-form-7' ),
      'default' => __( "Invalid Birthday Format.", 'contact-form-7' )
  ) ) );
}

add_action( 'admin_init', 'wpcf7_add_tag_generator_birthday', 15 );

function wpcf7_add_tag_generator_birthday() {
  if ( ! function_exists( 'wpcf7_add_tag_generator' ) )
    return;

  wpcf7_add_tag_generator( 'birthday', __( 'Birthday field', 'contact-form-7' ),
    'wpcf7-tg-pane-birthday', 'wpcf7_tg_pane_birthday' );
}

function wpcf7_tg_pane_birthday( $contact_form ) {
  wpcf7_tg_pane_birthday_and_relatives( 'birthday' );
}

function wpcf7_tg_pane_birthday_and_relatives( $type = 'birthday' ) {
?>
<div id="wpcf7-tg-pane-<?php echo $type; ?>" class="hidden">
  <form action="">
    <table>
      <tr>
        <td>
          <input type="checkbox" name="required" />&nbsp;
          <?php echo esc_html( __( 'Required field?', 'contact-form-7' ) ); ?>
        </td>
      </tr>
      <tr>
        <td>
          <?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?>
          <br />
          <input type="text" name="name" class="tg-name oneline" />
        </td>
        <td></td>
      </tr>
    </table>

    <table>
      <tr>
        <td>
          <code>id</code>
          (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)
          <br />
          <input type="text" name="id" class="idvalue oneline option" />
        </td>

        <td>
          <code>class</code>
          (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)
          <br />
          <input type="text" name="class" class="classvalue oneline option" />
        </td>
      </tr>

      <tr>
        <td>
          <code>size</code>
          (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)
          <br />
          <input type="number" name="size" class="numeric oneline option" min="1" />
        </td>

        <td>
          <code>maxlength</code>
          (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)
          <br />
          <input type="number" name="maxlength" class="numeric oneline option" min="1" />
        </td>
      </tr>

      <tr>
        <td colspan="2">
          <?php echo esc_html( __( 'Akismet', 'contact-form-7' ) ); ?>
          (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)
          <br />
          <input type="checkbox" name="akismet:author" class="option" />&nbsp;
          <?php echo esc_html( __( "This field requires author's name", 'contact-form-7' ) ); ?>
          <br />
        </td>
      </tr>

      <tr>
        <td>
          <?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?>
          (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)
          <br />
          <input type="text" name="values" class="oneline" />
        </td>

        <td>
          <br />
          <input type="checkbox" name="placeholder" class="option" />&nbsp;
          <?php echo esc_html( __( 'Use this text as placeholder?', 'contact-form-7' ) ); ?>
        </td>
      </tr>
    </table>

    <div class="tg-tag">
      <?php echo esc_html( __( "Copy this code and paste it into the form left.", 'contact-form-7' ) ); ?>
      <br />
      <input type="text" name="<?php echo $type; ?>" class="tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" />
    </div>

    <div class="tg-mail-tag">
      <?php echo esc_html( __( "And, put this code into the Mail fields below.", 'contact-form-7' ) ); ?>
      <br />
      <input type="text" class="mail-tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" />
    </div>

  </form>
</div>
<?php
}

?>
