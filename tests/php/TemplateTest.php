<?php
/**
 * Template Test
 *
 * @package VoxHash\WPTPN
 */

use VoxHash\WPTPN\Template;

class TemplateTest extends WP_UnitTestCase {
    
    private $template;
    
    public function setUp(): void {
        parent::setUp();
        $this->template = new Template();
    }
    
    public function test_token_replacement() {
        $post = $this->factory->post->create_and_get([
            'post_title' => 'Test Post',
            'post_content' => 'This is test content.',
            'post_author' => 1,
        ]);
        
        $template_text = 'New post: {post_title} by {post_author}';
        $result = $this->template->preview($template_text, $post->ID);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Test Post', $result['previews']['MarkdownV2']);
    }
    
    public function test_invalid_token() {
        $template_text = 'New post: {invalid_token}';
        $result = $this->template->validate($template_text);
        
        $this->assertFalse($result['valid']);
        $this->assertContains('Unknown token: invalid_token', $result['errors']);
    }
    
    public function test_markdown_v2_escaping() {
        $template_text = 'Test *bold* and _italic_ text';
        $result = $this->template->preview($template_text);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('\\*bold\\*', $result['previews']['MarkdownV2']);
    }
}
