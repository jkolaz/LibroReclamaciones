<?php
/*
Template Name: Libro de reclamaciones
*/
global $complaintsBookPublic;

get_header();

$opciones = get_field('cb_goods_or_services');

$corporateNames = $complaintsBookPublic->getCorporateNames();
?>
<div class="container">
    <form id="form-complaints-book" method="post" >
        <input type="hidden" name="action" value="register-complaints-book">
        <div class="section">
            <div class="form-title">
                <h2>Seleccionar Razón Social</h2>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="cbo-business-name"></label>
                    <div class="form-field">
                        <select name="cbo-business-name" id="cbo-business-name">
                            <option value="">- Seleccionar Razón Social -</option>
                            <?php
                            foreach ($corporateNames AS $corporateName) {
                                ?>
                                <option value="<?php echo $corporateName->id; ?>"><?php echo $corporateName->name . ', ' . $corporateName->business_name . ', RUC: ' . $corporateName->ruc . ', ' . $corporateName->address; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-title">
                <h2>Libro de Reclamaciones</h2>
            </div>
            <div class="form-row">
                <div class="form-column-middle">
                    <label for="txt-correlative"></label>
                    <input type="text" id="txt-correlative" name="txt-correlative" placeholder="Hoja de Reclamación [00000000]" value="" readonly />
                </div>
                <div class="form-column-middle">
                    <label for="txt-date"></label>
                    <input type="date" id="txt-date" name="txt-date" placeholder="<?php echo date('d/m/Y'); ?>" value="<?php echo date('Y-m-d'); ?>" readonly/>
                </div>
            </div>
            <div class="form-title">
                <h3>DATOS DEL CLIENTE</h3>
            </div>
            <div class="form-row">
                <div class="form-column-middle">
                    <label for="txt-name"></label>
                    <div class="form-field">
                        <input type="text" id="txt-name" name="txt-name" value="" placeholder="Nombres*"/>
                    </div>
                </div>
                <div class="form-column-middle">
                    <label for="txt-lastname"></label>
                    <div class="form-field">
                        <input type="text" id="txt-lastname" name="txt-lastname" value="" placeholder="Apellidos*"/>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-middle">
                    <label for="txt-phone"></label>
                    <div class="form-field">
                        <input type="text" id="txt-phone" name="txt-phone" value="" placeholder="Celular*"/>
                    </div>
                </div>
                <div class="form-column-middle">
                    <label for="txt-document-number"></label>
                    <div class="form-field">
                        <input type="text" id="txt-document-number" name="txt-document-number" value="" placeholder="DNI/CE*"/>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="txt-address"></label>
                    <div class="form-field">
                        <input type="text" id="txt-address" name="txt-address" value="" placeholder="Domicilio*"/>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="txt-email"></label>
                    <div class="form-field">
                        <input type="text" id="txt-email" name="txt-email" value="" placeholder="E-mail*"/>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="txt-tutor"></label>
                    <div class="form-field">
                        <input type="text" id="txt-tutor" name="txt-tutor" value="" placeholder="Padre o madre (opcional)"/>
                    </div>
                </div>
            </div>
            <div class="form-title">
                <h3>IDENTIFICACIÓN DEL BIEN CONTRATADO</h3>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="cbo-type-service"></label>
                    <div class="form-field">
                        <select name="cbo-type-service" id="cbo-type-service">
                            <option value="">- Seleccionar bien o servicio contratado -</option>
                            <option value="good">Bien</option>
                            <option value="service">Servicio</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="txt-amount"></label>
                    <div class="form-field">
                        <input type="text" id="txt-amount" name="txt-amount" value="" placeholder="Monto Reclamado"/>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="txt-description"></label>
                    <div class="form-field">
                        <textarea name="txt-description" id="txt-description" cols="30" rows="10" placeholder="Descripción*"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-title">
                <h3>DETALLE DE LA RECLAMACIÓN Y PEDIDO DEL COMSUMIDOR</h3>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <ul class="information">
                        <li>RECLAMO: Disconformidad relacionada a los productos o servicios.</li>
                        <li>QUEJA: Disconformidad no relacionada a los productos o servicios; o, malestar o descontento a la atención al público.</li>
                    </ul>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="cbo-type-claim"></label>
                    <div class="form-field">
                        <select name="cbo-type-claim" id="cbo-type-claim">
                            <option value="">- Seleccionar reclamo o queja -</option>
                            <option value="claim">Queja</option>
                            <option value="complaint">Reclamo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="txt-detail"></label>
                    <div class="form-field">
                        <textarea name="txt-detail" id="txt-detail" cols="30" rows="10" placeholder="Detalle*"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <label for="txt-request"></label>
                    <div class="form-field">
                        <textarea name="txt-request" id="txt-request" cols="30" rows="10" placeholder="Pedido*"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <input type="radio" id="rd-privacy-policy" name="rd-privacy-policy">
                    <label for="rd-privacy-policy"></label>
                    <label for="rd-privacy-policy" class="rd-privacy-policy-text">
                        Declaro haber leído y aceptado la <a href="#" target="_blank">Política de Privacidad</a>. Declaro que los datos
                        consignados son correctos y fiel expresión de la verdad.
                    </label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full content-button">
                    <button type="submit" id="btn-submit">Enviar</button>
                </div>
            </div>
            <div class="form-row">
                <div class="form-column-full">
                    <ul class="disclaimer">
                        <li>
                            *La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito prvio para interponer una denuncia ante INDECOPI.
                        </li>
                        <li>
                            *El proveedor deberá dar respuestas al reclamo en un plazo no mayor a treinta (15) días calendario.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
<?php
get_footer();