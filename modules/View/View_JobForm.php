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
                    var form = $('#request'),
                        data = form.serializeArray(),
                        options = {},
                        params = {};

                    for (var e in data) {
                        if (data.hasOwnProperty(e)) {
                            options[data[e].name] = data[e].value;
                        }
                    }

                    params.url = options.url;
                    delete options.url;
                    params.options = JSON.stringify(options);
                    window.location = '?' + $.param(params);
                    //console.log($.param(params));
                };
            })();
        </script>

            <div class="form">
                <form action="/" id="request" method="get" onsubmit="scrn.formSubmit();return false;">
                    <label>Адрес
                        <input name="url" />
                    </label>

                    <label>Ширина экрана
                        <input name="viewPortWidth" value="1280" />
                    </label>

                    <label>Ширина экрана
                        <input name="viewPortHeight" value="1024" />
                    </label>

                    <label>Обратная ссылка
                        <input name="callbackUri" value="" />
                    </label>

                    <label>Масштабировать ширину
                        <input name="resizeWidth" value="200" />
                    </label>

                    <label>Масштабировать высоту
                        <input name="resizeHeight" value="150" />
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