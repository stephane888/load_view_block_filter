<?php
namespace Drupal\load_view_block_filter\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Stephane888\Debug\debugLog;

/**
 * Provides a load view block filter block.
 * doc : https://newbedev.com/add-filter-criteria-in-views-programmatically
 *
 * @Block(
 *   id = "load_view_block_filter_load_view_block_filter",
 *   admin_label = @Translation("load view block filter"),
 *   category = @Translation("Custom")
 * )
 */
class LoadViewBlockFilterBlock extends BlockBase implements ContainerFactoryPluginInterface
{

    /**
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $RequestStack)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->request = $RequestStack->getCurrentRequest();
    }

    /**
     *
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static($configuration, $plugin_id, $plugin_definition, $container->get('request_stack'));
    }

    /**
     *
     * {@inheritdoc}
     */
    public function defaultConfiguration()
    {
        return [
            'foo' => $this->t('Hello world!')
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form['foo'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Foo'),
            '#default_value' => $this->configuration['foo']
        ];
        return $form;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->configuration['foo'] = $form_state->getValue('foo');
    }

    /**
     *
     * {@inheritdoc}
     */
    protected function blockAccess(AccountInterface $account)
    {
        // @DCG Evaluate the access condition here.
        $condition = TRUE;
        return AccessResult::allowedIf($condition);
    }

    /**
     *
     * {@inheritdoc}
     */
    public function build()
    {
        $query = $this->request->query->all();
        $view_id = 'listes_des_prestataires';
        $view_display = 'block_1';
        $view = \Drupal\views\Views::getView($view_id);
        $view->initHandlers();
        // $view->filter->query->addWhere('AND', 'field_domaine_de_competance_target_id', '1371', '=');
        // debugLog::kintDebugDrupal($view->filter['field_domaine_de_competance_target_id'], 'build-query');
        /**
         *
         * @var \Drupal\views_autocomplete_filters\Plugin\views\filter\ViewsAutocompleteFiltersString $filterTitle
         */

        foreach ($query as $key => $value) {
            if (! empty($view->filter[$key]) && ! empty($value)) {
                if (is_array($view->filter[$key]->value))
                    $view->filter[$key]->value = [
                        $value
                    ];
                else
                    $view->filter[$key]->value = $value;
            }

            // debugLog::kintDebugDrupal($view->filter[$key], 'build-' . $key);
        }
        return $view->buildRenderable($view_display, []);
    }
}
