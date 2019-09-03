<?php
/**
 * Script_Xtractor.php handles parsing and compiling inline JavaScript from Blade templates.
 *
 * @package Tsquare\ScriptXtractor;
 */

namespace Tsquare\ScriptXtractor;

/**
 * Class Script_Xtractor
 */
class Script_Xtractor {

	/**
	 * Path to the templates to be parsed.
	 *
	 * @var string
	 */
	protected $templates_path;

	/**
	 * Array of template files to be parsed.
	 *
	 * @var array
	 */
	public $files;

	/**
	 * Array of template blocks.
	 *
	 * @var array
	 */
	protected $blocks;

	/**
	 * Script_Xtractor constructor.
	 *
	 * @param string $path The path to the template files to be parsed.
	 */
	public function __construct( string $path ) {
		$this->templates_path = $path;
	}

	/**
	 * Scan the templates path, collecting all blade.php files in an array.
	 */
	public function scan_templates(): void {
		$this->files = $this->discover( $this->templates_path );
	}

	/**
	 * Collect an array containing all files contained in the templates path.
	 *
	 * @param string $directory The path for recursive file discovery.
	 * @param array  $files An array of blade.php files discovered.
	 *
	 * @return array
	 */
	public function discover( $directory, $files = [] ): array {

		foreach ( scandir( $directory ) as $item ) {
			if ( 0 !== stripos( strrev( $item ), 'php.edalb' ) ) {
				continue;
			}

			$item = $directory . '/' . $item;

			if ( ! is_file( $item ) ) {
				return self::discover( $item, $files );
			}

			$files[] = $item;
		}

		return $files;
	}

	/**
	 * Iterate over the template files and load them for parsing.
	 *
	 * @param array $files An array of files to be loaded and parsed.
	 */
	public function load( $files ): void {
		$blocks = [];
		foreach ( $files as $file ) {
			$template = file_get_contents( $file );
			$block    = $this->parse_template( $template );

			if ( $block ) {
				$blocks[] = $block;
			}
		}
		$this->blocks = array_flatten( $blocks );
	}

	/**
	 * Parse the template, extracting the our directives.
	 *
	 * @param string $template The loaded template file to parse.
	 *
	 * @return array|void $blocks Array of js template blocks.
	 */
	public function parse_template( $template ) {
		preg_match_all( '/(?:{{-- beginjs.*?endjs --}})/ims', $template, $blocks );

		if ( [] !== array_filter( $blocks ) ) {
			return array_flatten( $blocks );
		}
	}

	/**
	 * Get the parameters and the contents of the blocks, send them off to be compiled, and generate json enqueue config in the SC_ASSETS_PATH.
	 */
	public function process_blocks() {
		foreach ( $this->blocks as $block ) {
			$block_params = $this->extract_params( $block );
			$block_script = $this->extract_script( $block );
			$this->generate_script( $block_params, $block_script );
		}
	}

	/**
	 * Extract any optional parameters from a template block.
	 *
	 * @param string $block A js template block.
	 *
	 * @return array
	 */
	public function extract_params( $block ): array {
		preg_match_all( '/{{-- beginjs.+?--}}/im', $block, $matches );

		$match_string = array_flatten( $matches )[0];
		$match_array  = explode( ':', $match_string );

		$params['page']      = $match_array[1] ?? 'all';
		$params['priority']  = $match_array[2] ?? '50';
		$params['in_footer'] = ( 0 === stripos( $match_array[3], 'true' ) );

		return $params;
	}

	/**
	 * Extract the contents of the template block.
	 *
	 * @param string $block The template block.
	 *
	 * @return string
	 */
	public function extract_script( $block ): string {
		preg_match_all( '/--}}(.+?){{--/ims', $block, $script );

		return $script[1][0];
	}

	/**
	 * Generate files in a temporary location.
	 *
	 * @param array  $block_params  Parameters for page slug, priority, and whether to load in the footer.
	 * @param string $script The script to append the page's javascript file.
	 */
	public function generate_script( $block_params, $script ): void {
		// Append scripts to the appropriate page file, and generate a json config file for the enqueuer.
		// This should take place in a temporary directory that gets switched out at the end, just in case something fails.
	}
}
