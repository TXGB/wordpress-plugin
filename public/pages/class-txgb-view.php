<?php

/**
 * View-specific wrapper.
 * Limits the accessible scope available to templates.
 */
class TXGB_View {
    /**
     * Template being rendered.
     */
    protected $template = null;

    protected $data = [];

    /**
     * Initialize a new view context.
     */
    public function __construct($template, $data = [])
    {
        $this->template = $template . '.php';
        $this->data = $data;
    }

    /**
     * Render the template, returning it's content.
     * @param array $data Data made available to the view.
     * @return string The rendered template.
     */
    public function render()
    {
        extract($this->data);

        include($this->resolvePath());

        return isset($content) ? $content : '';
    }

    protected function resolvePath()
    {
        $template = locate_template( $this->template );
        $filtered = apply_filters( 'template_include',
            apply_filters( 'virtual_page_template', $template )
        );

        if ( empty( $filtered ) || file_exists( $filtered ) ) {
            $template = $filtered;
        }
        if ( ! empty( $template ) && file_exists( $template ) ) {
            return $template;
        }

        $template = plugin_dir_path(__DIR__) . '../templates/' . $this->template;

        if (file_exists($template)) {
            return $template;
        }

        return null;
    }
}
