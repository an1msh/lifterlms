<?php
/**
 * LLMS_Meta_Box_Achievement_Sync class
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta box to sync between awarded achievements and achievement templates.
 *
 * @since [version]
 */
class LLMS_Meta_Box_Achievement_Sync extends LLMS_Abstract_Meta_Box_User_Engagement_Sync {

	/**
	 * Type of user engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type = 'achievement';

	/**
	 * The post type of an awarded engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_awarded = 'llms_my_achievement';

	/**
	 * The post type of an engagement template.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_template = 'llms_achievement';

	/**
	 * Post types that this meta box should be added to.
	 *
	 * @var string[]
	 */
	public $screens = array(
		'llms_achievement', // Template.
		'llms_my_achievement', // Awarded.
	);

	/**
	 * Returns a translated text of the given type.
	 *
	 * @since [version]
	 *
	 * @param int   $text_type One of the LLMS_Abstract_Meta_Box_User_Engagement_Sync::TEXT_ constants.
	 * @param array $variables Optional variables that are used in sprintf().
	 * @return string
	 */
	protected function get_text( $text_type, $variables = array() ) {

		switch ( $text_type ) {
			case self::TEXT_SYNC_ALERT_MANY_AWARDED_ENGAGEMENTS:
				return sprintf(
					/* translators: 1: number of awarded achievements */
					__(
						'This action will replace the current title, content, background etc. of %1$d awarded achievements with the ones from this achievement template.\nAre you sure you want to proceed?',
						'lifterlms'
					),
					( $variables['awarded_number'] ?? 0 )
				);
			case self::TEXT_SYNC_ALERT_ONE_AWARDED_ENGAGEMENT:
				return sprintf(
					/* translators: 1: number of awarded achievements */
					__(
						'This action will replace the current title, content, background etc. of %1$d awarded achievement with the ones from this achievement template.\nAre you sure you want to proceed?',
						'lifterlms'
					),
					( $variables['awarded_number'] ?? 0 )
				);
			case self::TEXT_SYNC_ALERT_THIS_AWARDED_ENGAGEMENT:
				return __(
					'This action will replace the current title, content, background etc. of this awarded achievement with the ones from the achievement template.\nAre you sure you want to proceed?',
					'lifterlms'
				);
			case self::TEXT_SYNC_AWARDED_ENGAGEMENT:
				return __( 'Sync Awarded Achievement', 'lifterlms' );
			case self::TEXT_SYNC_AWARDED_ENGAGEMENTS:
				return __( 'Sync Awarded Achievements', 'lifterlms' );
			case self::TEXT_SYNC_DESCRIPTION_MANY_AWARDED_ENGAGEMENTS:
				return sprintf(
					/* translators: 1: number of awarded achievements */
					__( 'Sync %1$d awarded achievements with this achievement template.', 'lifterlms' ),
					( $variables['awarded_number'] ?? 0 )
				);
			case self::TEXT_SYNC_DESCRIPTION_ONE_AWARDED_ENGAGEMENT:
				return sprintf(
					/* translators: 1: number of awarded achievements */
					__( 'Sync %1$d awarded achievement with this achievement template.', 'lifterlms' ),
					( $variables['awarded_number'] ?? 0 )
				);
			case self::TEXT_SYNC_DESCRIPTION_THIS_AWARDED_ENGAGEMENT:
				return sprintf(
					/* translators: 1: link to edit the achievement template, 2: closing anchor tag */
					__( 'Sync this awarded achievement with its %1$sachievement template%2$s.', 'lifterlms' ),
					'<a href="' . get_edit_post_link( ( $variables['template_id'] ?? 0 ) ) . '" target="_blank">',
					'</a>'
				);
			case self::TEXT_SYNC_ENGAGEMENT_TEMPLATE_NO_AWARDED_ENGAGEMENTS:
				return __( 'This achievement template has no awarded achievements to sync.', 'lifterlms' );
			default:
				return parent::get_text( $text_type );
		}
	}
}
