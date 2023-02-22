<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    #[Route('/map', name: 'app_map')]
    public function index(): Response
    {
        return new Response(
            '<script>
                cacApp.load(
                    "map",
                    {
                        geoportalApiKey: "v89tabvpoy3qyoxbf6nv49hd",
                        geoportalAutoConf: "../Storage/autoconf.json",
                        language: "fr"
                    },
                    {
                        cacType: "cacre-ign-inside",
                        bgName: "OrthoParcelles",
                        scale: "5000",
                        tracks: [
                            "../Storage/map.kml"
                        ]
                    }
                );
            </script>
            <script type="text/javascript" src="http://cartealacarte.ign.fr/api.js"></script>
            <div class="map"></div>'
        );
    }
}
