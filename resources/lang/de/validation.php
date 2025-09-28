<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Das :attribute  muss akzeptiert werden.',
    'active_url' => 'Das :attribute  ist keine gültige URL.',
    'after' => 'Das :attribute  muss ein Datum nach: Datum sein.',
    'after_or_equal' => 'Das: :attribute  muss ein Datum danach sein oder gleich: Datum.',
    'alpha' => 'Das :attribute  muss nur Buchstaben enthalten.',
    'alpha_dash' => 'Das :attribute  muss nur Buchstaben, Zahlen, Striche und Unterstriche enthalten.',
    'alpha_num' => 'Das :attribute  muss nur Buchstaben und Zahlen enthalten.',
    'array' => 'Das :attribute  muss ein Array sein.',
    'before' => 'Das :attribute  muss ein Datum sein: Datum.',
    'before_or_equal' => 'Das :attribute  muss ein Datum vor oder gleich: Datum sein.',
    'between' => [
        'numeric' => 'Das :attribute  muss zwischen: min und: max.',
        'file' => 'Das: :attribute  muss zwischen: min und: max Kilobytes liegen.',
        'string' => 'Das :attribute  muss zwischen: min und: maxen Zeichen liegen.',
        'array' => 'Das :attribute  muss zwischen: min und: maximal items haben.',
    ],
    'boolean' => 'Das :attribute feld muss wahr oder falsch sein.',
    'confirmed' => 'Die: :attribute bestätigung stimmt nicht überein.',
    'current_password' => 'Das Passwort ist inkorrekt.',
    'date' => 'Das :attribute  ist kein gültiges Datum.',
    'date_equals' => 'Das: :attribute  muss ein Datum sein, das entspricht: Datum.',
    'date_format' => 'Das :attribute  stimmt nicht mit dem Format überein: Format.',
    'different' => 'Das :attribute  und: andere müssen anders sein.',
    'digits' => 'Das :attribute  muss: Ziffern Ziffern sein.',
    'digits_between' => 'Das: :attribute  muss zwischen: min und: maximal digits sein.',
    'dimensions' => 'Das :attribute  hat ungültige Bilddimensionen.',
    'distinct' => 'Das :attribute feld hat einen doppelten Wert.',
    'email' => 'Das :attribute  muss eine gültige E-Mail Adresse sein.',
    'ends_with' => 'Das :attribute  muss mit einem der folgenden :: Werte enden.',
    'exists' => 'Das ausgewählte :attribute  ist ungültig.',
    'file' => 'Das :attribute  muss eine Datei sein.',
    'filled' => 'Das :attribute feld muss einen Wert haben.',
    'gt' => [
        'numeric' => 'Das :attribute  muss größer sein als: Wert.',
        'file' => 'Das :attribute  muss größer sein als: Wert Kilobytes.',
        'string' => 'Das :attribute  muss größer sein als: Wertzeichen.',
        'array' => 'Das :attribute  muss mehr als: Wertelemente haben.',
    ],
    'gte' => [
        'numeric' => 'Das :attribute  muss größer oder gleich sein: Wert.',
        'file' => 'Das: :attribute  muss größer oder gleich sein: Wert Kilobyte.',
        'string' => 'Das :attribute  muss größer oder gleich sein: Wertzeichen.',
        'array' => 'Das :attribute  muss: Wertelemente oder mehr haben.',
    ],
    'image' => 'Das :attribute  muss ein Bild sein.',
    'in' => 'Das ausgewählte: :attribute  ist ungültig.',
    'in_array' => 'Das :attribute feld existiert nicht in: Andere.',
    'integer' => 'Das :attribute  muss eine Ganzzahl sein.',
    'ip' => 'Das :attribute  muss eine gültige IP -Adresse sein.',
    'ipv4' => 'Das :attribute  muss eine gültige IPv4 -Adresse sein.',
    'ipv6' => 'Das :attribute  muss eine gültige IPv6 -Adresse sein.',
    'json' => 'Das :attribute  muss eine gültige JSON -Zeichenfolge sein.',
    'lt' => [
        'numeric' => 'Das: :attribute  muss geringer sein als: Wert.',
        'file' => 'Das: :attribute  muss kleiner als: Wert Kilobytes.',
        'string' => 'Das :attribute  muss kleiner sein als: Wertzeichen.',
        'array' => 'Das :attribute  muss weniger als: Wertelemente haben.',
    ],
    'lte' => [
        'numeric' => 'Das: :attribute  muss geringer sein als oder gleich: Wert.',
        'file' => 'Das: :attribute  muss kleiner oder gleich sein: Wert Kilobyte.',
        'string' => 'Das :attribute  muss geringer sein als oder gleich: Wertzeichen.',
        'array' => 'Das :attribute  darf nicht mehr als: Wertelemente haben.',
    ],
    'max' => [
        'numeric' => 'Das :attribute  darf nicht größer sein als: max.',
        'file' => 'Das :attribute  darf nicht größer sein als: max Kilobyte.',
        'string' => 'Das: :attribute  darf nicht größer sein als: max. Zeichen.',
        'array' => 'Das :attribute  darf nicht mehr als: maxe Elemente haben.',
    ],
    'mimes' => 'Das :attribute  muss eine Datei vom Typ sein :: Werte.',
    'mimetypes' => 'Das :attribute  muss eine Datei vom Typ sein :: Werte.',
    'min' => [
        'numeric' => 'Das :attribute  muss mindestens: min sein.',
        'file' => 'Das: :attribute  muss mindestens: min Kilobytes sein.',
        'string' => 'Das :attribute  muss mindestens: min -Zeichen sein.',
        'array' => 'Das :attribute  muss mindestens: min -Elemente haben.',
    ],
    'multiple_of' => 'Das :attribute  muss ein Vielfaches von: Wert sein.',
    'not_in' => 'Das ausgewählte: :attribute  ist ungültig.',
    'not_regex' => 'Das :attribute format ist ungültig.',
    'numeric' => 'Das :attribute  muss eine Zahl sein.',
    'password' => 'Das Passwort ist inkorrekt.',
    'present' => 'Das :attribute feld muss vorhanden sein.',
    'regex' => 'Das :attribute format ist ungültig.',
    'required' => 'Das :attribute ist erforderlich.',
    'required_if' => 'Das :attribute feld ist erforderlich, wenn :other Wert :value.',
    'required_unless' => 'Das :attribute feld ist erforderlich, es sei denn: Andere sind in: Werte.',
    'required_with' => 'Das :attribute feld ist erforderlich, wenn: Werte vorhanden sind.',
    'required_with_all' => 'Das :attribute feld ist erforderlich, wenn: Werte vorhanden sind.',
    'required_without' => 'Das :attribute feld ist erforderlich, wenn: Werte nicht vorhanden sind.',
    'required_without_all' => 'Das :attribute feld ist erforderlich, wenn keiner der Werte vorhanden sind.',
    'prohibited' => 'Das :attribute feld ist verboten.',
    'prohibited_if' => 'Das :attribute feld ist untersagt, wenn: Andere: Wert.',
    'prohibited_unless' => 'Das :attribute feld ist verboten, es sei denn: Andere sind in: Werte.',
    'same' => 'Das :attribute  und: andere müssen übereinstimmen.',
    'size' => [
        'numeric' => 'Das :attribute  muss: Größe sein.',
        'file' => 'Das :attribute  muss sein: Größe Kilobytes.',
        'string' => 'Das :attribute  muss sein: Größe Zeichen.',
        'array' => 'Das :attribute  muss: Größenelemente enthalten.',
    ],
    'starts_with' => 'Das :attribute  muss mit einem der folgenden :: Werte beginnen.',
    'string' => 'Das :attribute  muss eine Zeichenfolge sein.',
    'timezone' => 'Das :attribute  muss eine gültige Zone sein.',
    'unique' => 'Das :attribute  wurde bereits genommen.',
    'uploaded' => 'Das :attribute  konnte nicht hochgeladen werden.',
    'url' => 'Das :attribute format ist ungültig.',
    'uuid' => 'Das :attribute  muss ein gültiges UUID sein.',

    'service_id_invalid' => 'Die ausgewählte service_id ist ungültig.',
    'coupon_code_not_found' => 'Wir konnten keinen: Code -Gutschein finden',
    'service_ids_required' => 'Mindestens eine Service -ID ist erforderlich.',

    'service_ids_array' => 'Die Service -IDs müssen als Array bereitgestellt werden.',
    'service_ids_exists' => 'Die Service -IDs müssen als Array bereitgestellt werden.',
    'is_multiple_serviceman_required' => 'Das Feld IsmultipleServiceman ist erforderlich.',
    'is_multiple_serviceman_boolean' => 'Das Feld IsmultipleServiceman muss ein Booleschen sein.',
    'required_servicemen_required' => 'Das erforderliche Feld benötigt, wenn IsmultipleServiceman wahr ist.',
    'required_servicemen_integer' => 'Das erforderliche Feld benötigt eine Ganzzahl.',
    'select_serviceman_required' => 'Das Feld Select_Serviceman ist erforderlich.',
    'select_serviceman_in' => 'Der ausgewählte SELECT_SERVICEMAN ist ungültig.Es muss entweder "user_choice" oder "app_choose" sein.',
    'select_date_time_required' => 'Das Feld Select_date_time ist erforderlich.',
    'select_date_time_in' => 'Die ausgewählte select_date_time ist ungültig.Es muss entweder "benutzerdefiniert" oder "AS_Provider" sein.',

    'providerId_exists' => 'Anbieter existiert nicht',
    'serviceId_exists' => 'Service existiert nicht',

    'user_is_not_provider' => 'Der Benutzer ist kein Anbieter.',
    'login_type_google_apple_or_phone' => 'Melden Sie sich mit dem Typ an, kann entweder Google oder Apple sein',
    'address_ids_exists' => 'Die Adresse mit ID: Wert existiert nicht.',

    'banner_images_required' => 'Bitte wählen Sie mindestens ein Bild.',
    'banner_type_required' => 'Bitte wählen Sie den Banner -Typ.',
    'banner_related_id_required' => 'Bitte wählen Sie den Typ der Bannerkategorie.',
    'blog_categories_required' => 'Das Feld Kategorien ist erforderlich',
    'zones_required' => 'Das Feld Zonen ist erforderlich',

    'commission_regex' => 'Geben Sie den Prozentsatz des Provisionssatzes zwischen 0 und 99,99 ein',
    'category_type' => 'Der Kategoriestyp kann entweder Blog oder Service sein',

    'user_id_required' => 'Bitte wählen Sie Anbieter aus.',
    'document_id_required' => 'Bitte wählen Sie Dokument.',
    'identity_no_required' => 'Dokumentnummer ist erforderlich.',

    'service_id_required' => 'Das Feld "Dienstleistungen" ist erforderlich',
    'start_end_date_required' => 'Das Feld Startdatum und Enddatum ist erforderlich',
    'image_required' => 'Erfordern mindestens ein Bild',

    'provider_id_required' => 'Das Feld Anbieter ist erforderlich',
    'service_id_required_if' => 'Das Feld der zugehörigen Dienste ist erforderlich, wenn zufällig verwandte Dienste ausgeschaltet sind.',
    'type' => 'Bitte wählen Sie einen Service -Typ aus',
    'price_required_if' => 'Das Preisfeld ist erforderlich',

    'type_in' => 'Tag -Typ kann entweder Post oder Produkt sein',
    'rate_regex' => 'Geben Sie einen Steuersatz zwischen 0 und 99,99 an.',

    'provider_id_exists' => 'Der ausgewählte Anbieter ist ungültig.',
    'gap_required' => 'Das Lückenfeld ist erforderlich.',
    'gap_integer' => 'Die Lücke muss eine Ganzzahl sein.',
    'gap_min' => 'Die Lücke muss mindestens 1 betragen.',
    'time_unit_required' => 'Das Zeiteinheitsfeld ist erforderlich.',
    'time_unit_in' => 'Die ausgewählte Zeiteinheit ist ungültig.',
    'time_slots_required' => 'Mindestens ein Zeitfenster ist erforderlich.',
    'time_slots_day_required' => 'Das Tagesfeld ist erforderlich.',
    'time_slots_day_in' => 'Der ausgewählte Tag ist ungültig.',
    'time_slots_start_time_required' => 'Das Feld Startzeit ist erforderlich.',
    'time_slots_start_time_date_format' => 'Die Startzeit stimmt nicht mit dem Format H: i überein.',
    'time_slots_end_time_required' => 'Das Endzeitfeld ist erforderlich.',
    'time_slots_end_time_date_format' => 'Die Endzeit stimmt nicht mit dem Format H: i überein.',
    'time_slots_end_time_after' => 'Die Endzeit muss nach der Startzeit erfolgen.',

    'payment_type_in' => 'Zahlungsart sollte PayPal oder Bank sein.',

    // new keys
    'invalid_address_id' => 'Ungültige Adress -ID',
    'user_not_exists' => 'Der Benutzer existiert nicht oder wird deaktiviert',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for :attribute es using the
    | convention ":attribute e.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given :attribute e rule.
    |
    */

    'custom' => [
        // update profile
        'name' => [
            'max' => 'Der Name darf nicht länger als :max Zeichen sein.',
        ],
        'email' => [
            'email' => 'Die E-Mail-Adresse muss eine gültige E-Mail-Adresse sein.',
            'unique' => 'Die E-Mail-Adresse ist bereits vergeben.',
        ],
        'phone' => [
            'required' => 'Die Telefonnummer ist erforderlich.',
            'digits_between' => 'Die Telefonnummer muss zwischen :min und :max Ziffern haben.',
            'unique' => 'Die Telefonnummer ist bereits vergeben.',
        ],
        'code' => [
            'required' => 'Der Code ist erforderlich.',
        ],
        'role_id' => [
            'exists' => 'Die ausgewählte Rolle ist ungültig.',
        ],

        //create address
        'country_id' => [
            'required' => 'Das Feld Land ist erforderlich.',
            'exists' => 'Das ausgewählte Land ist ungültig.',
        ],
        'state_id' => [
            'required' => 'Das Feld Bundesland ist erforderlich.',
            'exists' => 'Das ausgewählte Bundesland ist ungültig.',
        ],
        'city' => [
            'required' => 'Die Stadt ist erforderlich.',
            'string' => 'Die Stadt muss eine Zeichenkette sein.',
        ],
        'address' => [
            'required' => 'Die Adresse ist erforderlich.',
        ],
        'latitude' => [
            'required' => 'Der Breitengrad ist erforderlich.',
            'latitude_longitude' => 'Der Breitengrad muss eine gültige Latitude sein.',
        ],
        'longitude' => [
            'required' => 'Der Längengrad ist erforderlich.',
            'latitude_longitude' => 'Der Längengrad muss eine gültige Longitude sein.',
        ],
        'postal_code' => [
            'required' => 'Die Postleitzahl ist erforderlich.',
        ],
        'alternative_phone' => [
            'required_if' => 'Das alternative Telefon ist erforderlich, wenn der Rollentyp "Dienst" ist.',
        ],
        'alternative_name' => [
            'required_if' => 'Der alternative Name ist erforderlich, wenn der Rollentyp "Dienst" ist.',
        ],

        //update address
        'user_id' => [
            'nullable' => 'Die Benutzer -ID ist optional.',
            'exists' => 'Die ausgewählte Benutzer -ID ist ungültig.',
        ],
        'type' => [
            'required' => 'Der Typ ist erforderlich.',
            'string' => 'Der Typ muss eine Zeichenfolge sein.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation :attribute es
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our :attribute e placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    ':attribute es' => [],

];
