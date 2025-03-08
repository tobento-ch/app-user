<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);
 
namespace Tobento\App\User\Boot;

use Psr\Http\Message\ResponseInterface;
use Tobento\App\Http\Boot\ErrorHandler;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\TokenTransportInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\App\User\Exception\AuthorizationException;
use Tobento\App\User\Exception\InvalidateTokenException;
use Tobento\App\User\Exception\PermissionDeniedException;
use Tobento\App\User\Exception\RoleDeniedException;
use Tobento\App\User\Exception\TokenExpiredException;
use Tobento\Service\Requester\RequesterInterface;
use Tobento\Service\Responser\ResponserInterface;
use Tobento\Service\Routing\RouterInterface;
use Tobento\Service\Routing\UrlException;
use Throwable;

class HttpUserErrorHandler extends ErrorHandler
{
    public const INFO = [
        'boot' => [
            'Http User Error Handler',
        ],
    ];
    
    protected const HANDLER_PRIORITY = 3000;
    
    public function handleThrowable(Throwable $t): Throwable|ResponseInterface
    {
        $requester = $this->app->get(RequesterInterface::class);
        $responser = $this->app->get(ResponserInterface::class);
        
        if ($t instanceof TokenExpiredException) {
            return $requester->wantsJson()
                ? $this->renderJson(code: 403, message: $this->getMessage(419))
                : $this->renderView(code: 403, message: $this->getMessage(419));
        }
        
        if ($t instanceof InvalidateTokenException) {
            $tokenStorage = $this->app->get(TokenStorageInterface::class);
            $tokenTransport = $this->app->get(TokenTransportInterface::class);
            
            $response = $requester->wantsJson()
                ? $this->renderJson(code: 403, message: $this->getMessage(419))
                : $this->renderView(code: 403, message: $this->getMessage(419));
            
            $tokenStorage->deleteToken($t->token());
            
            return $tokenTransport->removeToken(
                token: $t->token(),
                request: $requester->request(),
                response: $response,
            );
        }
        
        if ($t instanceof AuthenticationException) {
            return $requester->wantsJson()
                ? $this->renderJson(code: 403)
                : $this->renderView(code: 403);
        }
        
        // if ($t instanceof PermissionDeniedException) {}
        // if ($t instanceof RoleDeniedException) {}

        if ($t instanceof AuthorizationException) {
            
            $redirectUri = $t->redirectUri();
            
            if (!empty($t->redirectRoute())) {
                try {
                    $redirectUri = (string) $this->app->get(RouterInterface::class)->url($t->redirectRoute());
                } catch (UrlException $e) {
                    //
                }
            }
 
            if (!is_null($redirectUri)) {
                if (!empty($t->getMessage())) {
                    $responser->messages()->add($t->messageLevel(), $t->getMessage());
                }

                return $responser->redirect(uri: $redirectUri);
            }
            
            return $requester->wantsJson()
                ? $this->renderJson(code: 403, message: $t->getMessage())
                : $this->renderView(code: 403, message: $t->getMessage());
        }
        
        return $t;
    }
}