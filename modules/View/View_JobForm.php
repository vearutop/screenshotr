<?php

class View_JobForm extends View_Hardcoded {
    public function render()
    {

        ?>
        <script>
            var scrn;
            (function(){
                scrn = {};
                scrn.formSubmit = function () {
                    var form = $('#request'), data = form.serializeArray();
                    console.log(data);

                };
            })();
        </script>

            <div class="form">
                <form action="/" id="request" method="get" onsubmit="scrn.formSubmit();return false;">
                    <label>Адрес
                        <input name="url" />
                    </label>

                    <label>Ширина экрана
                        <input name="options[viewPortWidth]" value="1280" />
                    </label>

                    <label>Ширина экрана
                        <input name="options[viewPortHeight]" value="1024" />
                    </label>

                    <label>Обратная ссылка
                        <input name="options[callbackUri]" value="" />
                    </label>

                    <label>Масштабировать ширину
                        <input name="options[resizeWidth]" value="200" />
                    </label>

                    <label>Масштабировать высоту
                        <input name="options[resizeHeight]" value="150" />
                    </label>


                    <button class="button" type="submit">Отправить</button>

                </form>


            </div>

        <style>
            .form input {
                display: block;
            }
        </style>

    <?php
    }
}