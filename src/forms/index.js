const { registerBlockType } = wp.blocks;
const { } = wp;

import { TextControl, TextareaControl, Button } from '@wordpress/components';
import { ReactComponent as Logo } from '../logo.svg';

registerBlockType('prueba/formularios', {
    title: 'Sugerencias',
    icon: { src: Logo },
    category: 'formularios',
    attributes: {
        nombre: {
            type: 'string',
            source: 'text',
            selector: '.jg-form-nombre',
        },

        apellidos: {
            type: 'string',
            source: 'text',
            selector: '.jg-form-apellidos',
        },
        email: {
            type: 'string',
            source: 'text',
            selector: '.jg-form-email',
        },
        sugerencia: {
            type: 'string',
            source: 'text',
            selector: '.jg-form-sugerencia',
        },


    },
    edit: (props) => {
        console.log(props);

        const { attributes: { nombre, apellidos, email, sugerencia }, setAttributes } = props;


        const onChangeTextNombre = (inputNombre) => {
            setAttributes({ nombre: inputNombre });
        }
        const onChangeTextApellidos = (inputApellidos) => {
            setAttributes({ apellidos: inputApellidos });
        }
        const onChangeTextEmail = (inputEmail) => {
            setAttributes({ email: inputEmail });
        }
        const onChangeTextSugerencia = (inputSugerencia) => {
            setAttributes({ sugerencia: inputSugerencia });
        }
        const onClickSugerencia = () => {
            console.log('Enviado')
            console.log(props)

            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
              }

            const url = "/wp-json/wp/v2/sugerencias";
            const body = JSON.stringify({
                "nombre": nombre,
                "apellidos": apellidos,
                "email": email,
                "post_content": sugerencia


            });
         
            fetch(url, {
                method: 'POST',
                credentials: "include",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: body

            });
            
        }
        return (
            <div className="jg-form">
                <h3>Sugerencias</h3>
                <div className="jg-form-nombre">
                    <TextControl
                        label='Nombre'
                        value={nombre}
                        onChange={onChangeTextNombre}
                    />
                </div>
                <div className="jg-form-apellidos">
                    <TextControl
                        label='Apellidos'
                        value={apellidos}
                        onChange={onChangeTextApellidos}
                    />
                </div>
                <div className="jg-form-email">
                    <TextControl
                        label='Email'
                        value={email}
                        onChange={onChangeTextEmail}
                    />
                </div>
                <div className="jg-form-sugerencia">
                    <TextareaControl
                        label='Sugerencia'
                        value={sugerencia}
                        onChange={onChangeTextSugerencia}
                    />
                </div>
                <div className="jg-form-boton">
                    <Button isSecondary onClick={onClickSugerencia} >Enviar</Button>
                </div>
            </div>
        )
    },
    save: (props) => {

        const { attributes: { nombre, apellidos } } = props;

        return (
            <div>
                <div className="jg-form">
                    <h3>Sugerencias</h3>
                    <div className="jg-form-nombre">
                        <TextControl.Content value={nombre} />
                    </div>
                    <div className="jg-form-apellidos">
                        <TextControl.Content value={apellidos} />
                    </div>
                </div>
            </div>
        )
    }

})