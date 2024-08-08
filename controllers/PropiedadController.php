<?php
namespace Controllers;

use MVC\Router;
use Model\Propiedad;
use Model\Vendedor;
use Intervention\Image\ImageManagerStatic as Image;

class PropiedadController {

    public static function index(Router $router) {
        $propiedades = Propiedad::all();
        $vendedores = Vendedor::all();
        $resultado = $_GET['resultado'] ?? null;

        $router->render('/propiedades/admin', [
            'propiedades'=> $propiedades,
            'vendedores' =>$vendedores,
            'resultado' => $resultado
        ]);
    }

    public static function crear(Router $router) {
        $propiedad = new Propiedad;
        $vendedores = Vendedor::all();
        $errores = Propiedad::getErrores();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            /**Crear nueva instancia**/
            $propiedad = new Propiedad($_POST['propiedad']);
            
            //generar nombre unico
            $nombreImagen = md5( uniqid( rand(), true ) ) . ".jpg";

            //setear imagen
            //realiza resize a imagen con intervention
            if($_FILES['propiedad']['tmp_name']['imagen']) {
                $image = Image::make($_FILES['propiedad']['tmp_name']['imagen'])->fit(800,600);
                $propiedad->setImagen($nombreImagen);
            }

            //validar
            $errores = $propiedad->validar();

            //revisar que array de errores esté vacio
            if(empty($errores)) {

                //Crear la carpeta para subir imagenes
                if(!is_dir(CARPETA_IMAGENES)) {
                    mkdir(CARPETA_IMAGENES);
                }

                //guarda imagen en servidor
                $image->save(CARPETA_IMAGENES . $nombreImagen);

                //guarda en la base de datos
                $propiedad->guardar();
            }
        }

        $router->render('/propiedades/crear', [
            'propiedad'=> $propiedad,
            'vendedores' => $vendedores,
            'errores' => $errores
        ]);
        
    }

    public static function actualizar(Router $router) {
        $id = validarORedireccionar('/admin');
        $propiedad = Propiedad::find($id);
        $vendedores = Vendedor::all();
        $errores = Propiedad::getErrores();

        if($_SERVER['REQUEST_METHOD'] === 'POST' ) {
            //Asignar atributos
            $args = $_POST['propiedad'];
            
            $propiedad->sincronizar($args);
    
            //Validación
            $errores = $propiedad->validar();
    
            //Subida de archivos
            //generar nombre unico
            $nombreImagen = md5( uniqid( rand(), true ) ) . ".jpg";
    
            if($_FILES['propiedad']['tmp_name']['imagen']) {
                $image = Image::make($_FILES['propiedad']['tmp_name']['imagen'])->fit(800,600);
                $propiedad->setImagen($nombreImagen);
            }
    
            //revisar que array de errores esté vacio
            if(empty($errores)) {
                // Almacenar la imagen
                if($_FILES['propiedad']['tmp_name']['imagen']) {
                    $image->save(CARPETA_IMAGENES . $nombreImagen);
                }
                
                $propiedad->guardar();
            }
        }

        $router->render('/propiedades/actualizar', [
            'propiedad'=> $propiedad,
            'vendedores' => $vendedores,
            'errores' => $errores
        ]);
    }

    public static function eliminar() {
       
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo = $_POST['tipo'];
    
            // peticiones validas
            if(validarTipoContenido($tipo) ) {
                // Leer el id
                $id = $_POST['id'];
                $id = filter_var($id, FILTER_VALIDATE_INT);

                // encontrar y eliminar la propiedad
                $propiedad = Propiedad::find($id);
                $resultado = $propiedad->eliminar();

                // Redireccionar
                if($resultado) {
                    header('location: /propiedades');
                }
            }
        }
    }
}