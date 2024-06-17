<?php

/*
    @author  Pablo Bozzolo boctulus@gmail.com
*/

namespace boctulus\SW\core\libs;

/**
 * Classe per gestire le sottoscrizioni WooCommerce.
 * 
 * @author Pablo Bozzolo < boctulus@gmail.com >
 */
class WCSubscriptions
{    
    /**
     * Costruttore della classe.
     * 
     * Verifica se la funzione wcs_get_users_subscriptions è disponibile, altrimenti lancia un'eccezione.
     */
    function __construct(){
        if (!function_exists('wcs_get_users_subscriptions'))
        {
            throw new \Exception("WooCommerce Subscriptions è richiesto");
        }
    }

    /*
        Riceve l'ID del post dal record di abbonamento e restituisce l'ID dell'utente.
    */
    function getUserSubscriptor($subscription_id)
    {
        $subscription = wcs_get_subscription( $subscription_id );
        
        return $subscription->get_user_id();
    }

    /*  
        Restituisce le sottoscrizioni.

        Accetta user_id e status come filtri.
    */
    function get($user_id = null, $status = null)
    {
        $subs = [];

        $uids    = ($user_id == null) ? Users::getUserIDList() : [ $user_id ];

        foreach ($uids as $user_id){
            $subscriptions = wcs_get_users_subscriptions( $user_id );
            
            foreach ($subscriptions as $subscription) {
                // filtro. Potrebbe essere "active", "on-hold" o "cancelled"
                if ($status != null && $subscription->get_status() != $status) {
                    continue;
                }

                $subs[] = [
                    'id'      => $subscription->get_id(),
                    'status'  => $subscription->get_status(),
                    'order'   => $subscription->get_order_number(),
                    'user_id' => !empty($user_id) ? $user_id : $subscription->get_user_id(), 
                ];
            }
        }

        return $subs;
    }

    /**
     * Verifica se un utente ha una sottoscrizione attiva in WooCommerce Subscriptions.
     *
     * @param int $user_id L'ID dell'utente.
     * @return bool True se l'utente ha una sottoscrizione attiva, altrimenti False.
     * 
     * Nome precedente: isActive
     */
    function hasActive($user_id = null) {
        // Controlla se l'utente ha sottoscrizioni attive escludendo gli stati "on-hold" e "cancelled".
        $subscriptions = wcs_get_users_subscriptions( $user_id );
        
        $active_subscriptions = [];
    
        foreach ($subscriptions as $subscription) {
            if ($user_id == null){
                $user_id = $subscription->get_user_id();
            }

            // Verifica che lo stato della sottoscrizione non sia "on-hold" né "cancelled".
            if ( $subscription->get_status() == 'active' ) {
                $active_subscriptions[] = $subscription;
            }
        }
    
        // Se ci sono sottoscrizioni attive dopo aver escluso gli stati "on-hold" e "cancelled", l'utente ha una sottoscrizione attiva.
        if ( ! empty( $active_subscriptions ) ) {
            return true;
        }
    
        return false;
    }
    
}