<?php

namespace Drupal\prelinker\Render;

use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;

/**
 * Adds Server Push link headers.
 */
class HeaderProcessor implements AttachmentsResponseProcessorInterface
{
    /**
     * The decorated HTML response attachments processor service.
     *
     * @var \Drupal\Core\Render\AttachmentsResponseProcessorInterface
     */
    protected $attachmentsProcessor;

    /**
     * Constructs a HtmlResponseAttachmentsProcessor object.
     *
     * @param \Drupal\Core\Render\AttachmentsResponseProcessorInterface $attachmentsProcessor
     */
    public function __construct(AttachmentsResponseProcessorInterface $attachmentsProcessor)
    {
        $this->attachmentsProcessor = $attachmentsProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function processAttachments(AttachmentsInterface $response)
    {
        $response = $this->attachmentsProcessor->processAttachments($response);

        // don't add to admin pages
        if (\Drupal::service('router.admin_context')->isAdminRoute()) {
            return $response;
        }

        $config = \Drupal::configFactory()->getEditable('prelinker.settings');

        $links = [];

        // preconnects
        if ($config->get('preconnect_push') && !$config->get('preconnect_head')) {
            $storage = \Drupal::entityTypeManager()->getStorage('preconnect');
            $query = $storage->getQuery()->sort('weight', 'ASC')->accessCheck(FALSE)->execute();

            $preconnects = $storage->loadMultiple($query);

            foreach ($preconnects as $preconnect) {
                if (\Drupal::service('prelinker.prelinker')->check_page($preconnect->get('pages'))) {
                    $links[] = '<//'.$preconnect->get('domain').'>; rel="preconnect"';
                }
            }
        }

        // preloads
        if ($config->get('preload_push') && !$config->get('preload_head')) {
            $storage = \Drupal::entityTypeManager()->getStorage('preload');
            $query = $storage->getQuery()->sort('weight', 'ASC')->accessCheck(FALSE)->execute();

            $preloads = $storage->loadMultiple($query);

            foreach ($preloads as $preload) {
                if (\Drupal::service('prelinker.prelinker')->check_page($preload->get('pages'))) {
                    $links[] = '<'.$preload->get('file').'>; rel="preload"; as="'.$preload->get('as')
                        .($preload->get('as') == 'font'? '; crossorigin=crossorigin': '').'"';
                }
            }
        }

        // assign some config entries to variables to save us having to keep calling ->get()
        $headImage = $config->get('preload_head_image');
        $pushImage = $config->get('preload_push_image');
        $pushCSS = $config->get('preload_push_css');
        $pushPreload = $config->get('preload_push_preload');
        $pushPreconnect = $config->get('preload_push_preconnect');

        if ($headImage || $pushImage || $pushCSS || $pushPreload || $pushPreconnect) {
            $html = $response->getContent();
            $content = new \DOMDocument();
            $content->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
            $xpath = new \DOMXPath($content);

            // go through the content and look for data-preload-image=""
            if ($headImage || $pushImage) {
                $imagePush = [];
                $imageLinks = [];
                foreach ($xpath->query('///@data-preload-image') as $element) {
                    $imagePush[] = '<'.$element->value.'>; rel="preload"; as="image"';
                    $imageLinks[] = '<link rel="preload" href="'.$element->value.'" as="image">';
                }
                if ($pushImage && $imagePush) {
                    $links = array_merge($links, $imagePush);
                }
                if ($headImage && $imageLinks) {
                    $html = str_replace('<head>', '<head>'."\r\n".implode("\r\n", $imageLinks), $html);
                    $response->setContent($html);
                }
            }

            if ($pushCSS || $pushPreload || $pushPreconnect) {
                foreach ($content->getElementsByTagName('link') as $element) {
                    // go through and  existing preload <link rel="preload" to the response header
                    if ($pushPreload && $element->attributes->getNamedItem('rel')->value == 'preload') {
                        $links[] = '<'
                            .$element->attributes->getNamedItem('href')->value.'>; rel="preload"; as="'
                            .$element->attributes->getNamedItem('as')->value
                            .'"'
                            .($element->attributes->getNamedItem('crossorigin') ? '; crossorigin=crossorigin' : '');
                    }
                    // go through and add existing preconnect <link rel="preconnect" to the response header
                    if ($pushPreconnect && $element->attributes->getNamedItem('rel')->value == 'preconnect') {
                        $links[] = '<'
                            .$element->attributes->getNamedItem('href')->value.'>; rel="preconnect"';
                    }
                    // go through and add existing stylesheets <link rel="stylesheet" to the response header
                    if ($pushCSS && $element->attributes->getNamedItem('rel')->value == 'stylesheet') {
                        $links[] = '<'.$element->attributes->getNamedItem('href')->value.'>; rel="preload"; as="style"';
                    }
                }
            }

            // go through and add CSS files from @import
            if ($pushCSS) {
                foreach ($xpath->query('//*[@media="all" or @media="screen"]') as $element) {
                    $matches = [];
                    preg_match_all('/@import url\(["\']\/(.*)["\']\)/', $element->textContent, $matches);
                    if (isset($matches[1])) {
                        foreach ($matches[1] as $url) {
                            $links[] = '</'.$url.'>; rel="preload"; as="style"';
                        }
                    }
                }
            }
        }

        // new links to add to the headers?
        if ($links) {
            $current = $response->headers->get('Link', null, false);
            $merged = array_unique(array_merge($current, $links));
            $response->headers->set('Link', $merged, true);
        }

        return $response;
    }
}
