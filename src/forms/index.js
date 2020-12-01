const { registerBlockType } = wp.blocks;

import { ReactComponent as Logo } from '../logo.svg';

registerBlockType('prueba/formularios', {
    title: 'Sugerencias',
    icon: { src: Logo},
    category: 'formularios',
    edit: () => {
       

        return(
            <div className="jg-form">
                
                <h4>Nombre <input className="suggestImput" type="text" name="nombre"/></h4>
                <h4>Apellidos <input className="suggestImput" type="text" name="apellidos"/></h4>
                
                <button type="submit" className="suggestButton">Enviar</button>

                
            </div>
        )
    },
    save: () => {
        return(
            <div>
                
            </div>
        )
    }

})