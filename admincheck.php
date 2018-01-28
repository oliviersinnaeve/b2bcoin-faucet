<?php

/*
 * B2BCoin.xyz
 * https://faucetinabox.com/
 *
 * Copyright (c) 2014-2016 LiveHome Sp. z o. o.
 *
 * This file is part of B2BCoin.xyz.
 *
 * B2BCoin.xyz is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * B2BCoin.xyz is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with B2BCoin.xyz.  If not, see <http://www.gnu.org/licenses/>.
 */


function getRequestData() {
    if (array_key_exists("encoded_data", $_POST)) {
        return $_POST["encoded_data"];
    }

    return $req;
}

header("Content-Type: application/json");
die(json_encode(array("req_length" => strlen(getRequestData()))));
