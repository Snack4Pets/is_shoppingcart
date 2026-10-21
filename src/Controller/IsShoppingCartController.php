<?php

declare(strict_types=1);

namespace Oksydan\IsShoppingcart\Controller;

use Oksydan\IsShoppingcart\Translations\TranslationDomains;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IsShoppingCartController extends FrameworkBundleAdminController
{
    private ?FormHandlerInterface $formHandler = null;

    public function __construct(?FormHandlerInterface $formHandler = null)
    {
        $this->formHandler = $formHandler;
    }

    #[AdminSecurity("is_granted(['read'], request.get('_legacy_controller'))", message: 'You do not have permission to access this.')]
    public function index(): Response
    {
        $configurationForm = $this->getFormHandler()->getForm();

        return $this->render('@Modules/is_shoppingcart/views/templates/admin/index.html.twig', [
            'translationDomain' => TranslationDomains::TRANSLATION_DOMAIN_ADMIN,
            'configurationForm' => $configurationForm->createView(),
            'help_link' => false,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    #[AdminSecurity("is_granted('update', request.get('_legacy_controller')) && is_granted('create', request.get('_legacy_controller'))", message: 'You do not have permission to update this.', redirectRoute: 'is_shoppingcart_controller')]
    public function saveConfiguration(Request $request): Response
    {
        $redirectResponse = $this->redirectToRoute('is_shoppingcart_controller');

        $form = $this->getFormHandler()->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $redirectResponse;
        }

        if ($form->isValid()) {
            $data = $form->getData();
            $saveErrors = $this->getFormHandler()->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

                return $redirectResponse;
            }
        }

        $formErrors = [];

        foreach ($form->getErrors(true) as $error) {
            $formErrors[] = $error->getMessage();
        }

        $this->flashErrors($formErrors);

        return $redirectResponse;
    }

    private function getFormHandler(): FormHandlerInterface
    {
        if ($this->formHandler instanceof FormHandlerInterface) {
            return $this->formHandler;
        }

        /** @var FormHandlerInterface */
        return $this->get('oksydan.is_shoppingcart.configuration.form_handler');
    }
}
