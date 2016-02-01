<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Metabox_Field Parent Class
*
* Contains base code for each of the Metabox Fields
*
* @author codeBOX
* @project lifterLMS
*/
abstract class LLMS_Metabox_Field
{
	/**
	 * Global array used in class instance to store field information
	 * @var array
	 */
	public $field;

	/**
	 * Global varaible to contain meta information about $field
	 * @var object
	 */
	public $meta;

	/**
	 * Returns the information for a certain value in $field
	 * @param string $name Key to lookup in $field
	 * @return object
	 */
	public function GetField($name)
	{
		return $this->field[$name];
	}

	/**
	 * Updates a value inside $field
	 * @param string $name  Key to reference in $field
	 * @param object $value Updated value for key
	 * @return void
	 */
	public function SetField($name, $value)
	{
		$this->field[$name] = $value;
	}

	/**
	 * Outputs the head for each of the field types
	 * @todo  all the unset variables here should be defaulted somewhere else probably
	 */
	public function Output()
	{
		global $post;
		$this->meta = self::get_post_meta($post->ID, $this->field['id']);

		if( !isset( $this->field['group'] ) ) {
			$this->field['group'] = '';
		}

		if( !isset( $this->field['desc_class'] ) ) {
			$this->field['desc_class'] = '';
		}

		if( !isset( $this->field['desc'] ) ) {
			$this->field['desc'] = '';
		}

		?>
		<li class="llms-mb-list <?php echo $this->field['group']; ?>">
		<!--label and description-->
		<div class="description <?php echo $this->field['desc_class']; ?>">
			<label for="<?php echo $this->field['id']; ?>"><?php echo $this->field['label']; ?></label>
			<?php echo $this->field['desc'] ?>
		</div> <?php
	}

	/**
	 * Outputs the tail for each of the field types
	 */
	public function CloseOutput()
	{
		?> <div class="clear"></div></li> <?php
	}

	/**
	 * TBH I'm not sure exactly what this does... But removing it makes everything break.
	 * Your best bet is to ask Mark...
	 *
	 * @param  [type]
	 * @param  [type]
	 * @return [type]
	 */
	public static function get_post_meta($post_id, $field_id) {

		if ( $field_id === '_post_course_difficulty' ) {
			$difficulties = wp_get_object_terms($post_id, 'course_difficulty');

			if ( $difficulties ) {
				return $difficulties[0]->slug;
			}

		} else {
			return get_post_meta($post_id, $field_id, true);
		}

	}
}