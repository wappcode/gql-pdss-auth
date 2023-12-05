<?php

namespace GPDAuth\Models;

use DateTime;


class AuthSession
{

    /**
     * Issuer
     *
     * @var string
     */
    private $iss;
    /**
     * Subject 
     * The "sub" (subject) claim identifies the principal that is the
     * subject of the JWT.  The claims in a JWT are normally statements
     * about the subject.  The subject value MUST either be scoped to be
     * locally unique in the context of the issuer or be globally unique.
     * The processing of this claim is generally application specific.  The
     * "sub" value is a case-sensitive string containing a StringOrURI
     * value.  Use of this claim is OPTIONAL.
     *
     * @var string
     */
    private $sub;
    /**
     * Audience (allowed clients)
     *
     * @var ?array [string]
     */
    private $aud;
    /**
     * Expiration Time
     *
     * @var ?DateTime
     */
    private $exp;
    /**
     * Not Before
     *
     * @var ?DateTime
     */
    private $nbf;
    /**
     * Issued At
     *
     * @var ?DateTime
     */
    private $iat;
    /**
     * JWT ID
     *
     * @var ?string
     */
    private $jti;
    /**
     * Full name
     *
     * @var string
     */
    private $name;
    /**
     * Given name(s) or first name(s)
     *
     * @var ?string
     */
    private $given_name;
    /**
     * Surname(s) or last name(s)
     *
     * @var ?string
     */
    private $family_name;
    /**
     * Middle name(s)
     *
     * @var ?string
     */
    private $middle_name;
    /**
     * Casual name
     *
     * @var ?string
     */
    private $nickname;
    /**
     * Shorthand name by which the End-User wishes to be referred to
     *
     * @var string
     */
    private $preferred_username;
    /**
     * Profile page URL
     *
     * @var ?string
     */
    private $profile;
    /**
     * Profile picture URL
     *
     * @var ?string
     */
    private $picture;
    /**
     * Web page or blog URL
     *
     * @var ?string
     */
    private $website;
    /**
     * Preferred e-mail address
     *
     * @var ?string
     */
    private $email;
    /**
     * True if the e-mail address has been verified; otherwise false
     *
     * @var ?bool
     */
    private $email_verified;
    /**
     * Gender
     *
     * @var ?string
     */
    private $gender;
    /**
     * Birthday
     *
     * @var ?string
     */
    private $birthdate;
    /**
     * Time zone
     *
     * @var ?string
     */
    private $zoneinfo;
    /**
     * Locale
     *
     * @var ?string
     */
    private $locale;
    /**
     * Preferred telephone number
     *
     * @var ?string
     */
    private $phone_number;
    /**
     * True if the phone number has been verified; otherwise false
     *
     * @var ?bool
     */
    private $phone_number_verified;
    /**
     * Preferred postal address
     *
     * @var ?string
     */
    private $address;
    /**
     * Time the information was last updated
     *
     * @var ?DateTime
     */
    private $updated_at;

    /**
     * Value used to associate a Client session with an ID Token (MAY also be used for nonce values in other applications of JWTs)
     *
     * @var ?string
     */
    private $nonce;
    /**
     * Time when the authentication occurred
     *
     * @var ?DateTime
     */
    private $auth_time;

    /**
     * Session ID
     *
     * @var ?string
     */
    private $sid;

    /**
     * Scope Values (example  "scope":"email profile phone address")
     *
     * @var ?string
     */
    private $scope;
    /**
     * The client_id claim carries the client identifier of the OAuth 2.0 [RFC6749] client that requested the token
     *
     * @var ?string
     */
    private $client_id;


    /**
     * Expires in  Lifetime of the token in seconds
     * from the time the RS first sees it.  Used to implement a weaker
     * from of token expiration for devices that cannot synchronize their
     * internal clocks.
     *
     * @var ?int
     */
    private $exi;
    /**
     * A list of roles for the user that collectively represent who the
     * user is, e.g., "Student", "Faculty".  No vocabulary or syntax is
     * specified, although it is expected that a role value is a String
     * or label representing a collection of entitlements.  This value
     * has no canonical types.
     *
     * @var ?array [string]
     */
    private $roles;
    /**
     * A list of groups to which the user belongs, either through direct
     * membership, through nested groups, or dynamically calculated.  The
     * values are meant to enable expression of common group-based or
     * role-based access control models, although no explicit
     * authorization model is defined.  It is intended that the semantics
     * of group membership and any behavior or authorization granted as a
     * result of membership are defined by the service provider.  The
     * canonical types "direct" and "indirect" are defined to describe
     * how the group membership was derived.  Direct group membership
     * indicates that the user is directly associated with the group and
     * SHOULD indicate that clients may modify membership through the
     * "Group" resource.  Indirect membership indicates that user
     * membership is transitive or dynamic and implies that clients
     * cannot modify indirect group membership through the "Group"
     * resource but MAY modify direct group membership through the
     * "Group" resource, which may influence indirect memberships.  If
     * the SCIM service provider exposes a "Group" resource, the "value"
     *
     * @var ?array [string]
     */
    private $groups;
    /**
     * A list of entitlements for the user that represent a thing the
     * user has.  An entitlement may be an additional right to a thing,
     * object, or service.  No vocabulary or syntax is specified; service
     * providers and clients are expected to encode sufficient
     * information in the value so as to accurately and without ambiguity
     * determine what the user has access to.  This value has no
     * canonical types, although a type may be useful as a means to scope
     * entitlements.
     *
     * @var ?array [string]
     */
    private $entitlements;

    /**
     * The geographic location (TEMPORARY - registered 2022-03-23, extension registered 2023-02-13, expires 2024-03-23)	
     *
     * @var ?string
     */
    private $location;

    /**
     * A structured Claim representing the End-User's place of birth.	
     *
     * @var ?string
     */
    private $place_of_birth;
    /**
     * String array representing the End-User's nationalities.
     *
     * @var ?string
     */
    private $nationalities;
    /**
     * Family name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the family name(s) later in life for any reason. Note that in some cultures, people can have multiple family names or no family name; all can be present, with the names being separated by space characters.
     *
     * @var ?string
     */
    private $birth_family_name;
    /**
     * Given name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the given name later in life for any reason. Note that in some cultures, people can have multiple given names; all can be present, with the names being separated by space characters.
     *
     * @var ?string
     */
    private $birth_given_name;
    /**
     * Middle name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the middle name later in life for any reason. Note that in some cultures, people can have multiple middle names; all can be present, with the names being separated by space characters. Also note that in some cultures, middle names are not used.
     *
     * @var ?string
     */
    private $birth_middle_name;
    /**
     * End-User's salutation, e.g., "Mr."
     *
     * @var ?string
     */
    private $salutation;
    /**
     * End-User's title, e.g., "Dr."
     *
     * @var ?string
     */
    private $title;
    /**
     * End-User's mobile phone number formatted according to ITU-T recommendation
     *
     * @var ?string
     */
    private $msisdn;
    /**
     * Stage name, religious name or any other type of alias/pseudonym with which a person is known in a specific context besides its legal name. This must be part of the applicable legislation and thus the trust framework (e.g., be an attribute on the identity card).
     *
     * @var ?string
     */
    private $also_known_as;



    public function __construct()
    {
        $this->iat = new DateTime();
    }

    public function toArray()
    {
        $data = get_object_vars($this);
        // Convierte las fechas a formato timestamp
        $data = array_map(function ($item) {
            if ($item instanceof DateTime) {
                return $item->getTimestamp();
            }
        }, $data);
        return $data;
    }
    public function fillFromArray(array $data)
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {

                if (in_array($property, ["iat", "nbf", "exp"])) {
                    if (!empty($value) && is_numeric($value)) {
                        $this->$property = $this->timestampToDate($value);
                    }
                } else {
                    $this->$property = $value;
                }
            }
        }
    }
    /**
     *
     * @param integer $timestamp
     * @return DateTime
     */
    private function timestampToDate(int $timestamp): DateTime
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        return $date;
    }

    /**
     * Get issuer
     *
     * @return  string
     */
    public function getIss()
    {
        return $this->iss;
    }

    /**
     * Set issuer
     *
     * @param  ?string  $iss  Issuer
     *
     * @return  self
     */
    public function setIss(string $iss)
    {
        $this->iss = $iss;

        return $this;
    }

    /**
     * Get subject
     *
     * @return  string
     */
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * Set subject
     *
     * @param  ?string  $sub  Subject
     *
     * @return  self
     */
    public function setSub(string $sub)
    {
        $this->sub = $sub;

        return $this;
    }

    /**
     * Get [string]
     *
     * @return  ?array
     */
    public function getAud()
    {
        return $this->aud;
    }

    /**
     * Set [string]
     *
     * @param  ?array  $aud  [string]
     *
     * @return  self
     */
    public function setAud(?array $aud)
    {
        $this->aud = $aud;

        return $this;
    }

    /**
     * Get expiration Time
     *
     * @return  ?DateTime
     */
    public function getExp()
    {
        return $this->exp;
    }

    /**
     * Set expiration Time
     *
     * @param  ?DateTime  $exp  Expiration Time
     *
     * @return  self
     */
    public function setExp(?DateTime $exp)
    {
        $this->exp = $exp;

        return $this;
    }

    /**
     * Get not Before
     *
     * @return  ?DateTime
     */
    public function getNbf()
    {
        return $this->nbf;
    }

    /**
     * Set not Before
     *
     * @param  ?DateTime  $nbf  Not Before
     *
     * @return  self
     */
    public function setNbf(?DateTime $nbf)
    {
        $this->nbf = $nbf;

        return $this;
    }

    /**
     * Get issued At
     *
     * @return  ?DateTime
     */
    public function getIat()
    {
        return $this->iat;
    }
    /**
     * Set issued At
     *
     * @param DateTime $iat
     * @return self
     */
    public function setIat(DateTime $iat)
    {
        $this->iat = $iat;
        return $this;
    }

    /**
     * Get jWT ID
     *
     * @return  ?string
     */
    public function getJti()
    {
        return $this->jti;
    }

    /**
     * Set jWT ID
     *
     * @param  ?string  $jti  JWT ID
     *
     * @return  self
     */
    public function setJti(?string $jti)
    {
        $this->jti = $jti;

        return $this;
    }

    /**
     * Get full name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set full name
     *
     * @param  string  $name  Full name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get given name(s) or first name(s)
     *
     * @return  ?string
     */
    public function getGiven_name()
    {
        return $this->given_name;
    }

    /**
     * Set given name(s) or first name(s)
     *
     * @param  ?string  $given_name  Given name(s) or first name(s)
     *
     * @return  self
     */
    public function setGiven_name(?string $given_name)
    {
        $this->given_name = $given_name;

        return $this;
    }

    /**
     * Get surname(s) or last name(s)
     *
     * @return  ?string
     */
    public function getFamily_name()
    {
        return $this->family_name;
    }

    /**
     * Set surname(s) or last name(s)
     *
     * @param  ?string  $family_name  Surname(s) or last name(s)
     *
     * @return  self
     */
    public function setFamily_name(?string $family_name)
    {
        $this->family_name = $family_name;

        return $this;
    }

    /**
     * Get middle name(s)
     *
     * @return  ?string
     */
    public function getMiddle_name()
    {
        return $this->middle_name;
    }

    /**
     * Set middle name(s)
     *
     * @param  ?string  $middle_name  Middle name(s)
     *
     * @return  self
     */
    public function setMiddle_name(?string $middle_name)
    {
        $this->middle_name = $middle_name;

        return $this;
    }

    /**
     * Get casual name
     *
     * @return  ?string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Set casual name
     *
     * @param  ?string  $nickname  Casual name
     *
     * @return  self
     */
    public function setNickname(?string $nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get shorthand name by which the End-User wishes to be referred to
     *
     * @return  string
     */
    public function getPreferred_username()
    {
        return $this->preferred_username;
    }

    /**
     * Set shorthand name by which the End-User wishes to be referred to
     *
     * @param  string  $preferred_username  Shorthand name by which the End-User wishes to be referred to
     *
     * @return  self
     */
    public function setPreferred_username(string $preferred_username)
    {
        $this->preferred_username = $preferred_username;

        return $this;
    }

    /**
     * Get profile page URL
     *
     * @return  ?string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile page URL
     *
     * @param  ?string  $profile  Profile page URL
     *
     * @return  self
     */
    public function setProfile(?string $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile picture URL
     *
     * @return  ?string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set profile picture URL
     *
     * @param  ?string  $picture  Profile picture URL
     *
     * @return  self
     */
    public function setPicture(?string $picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get web page or blog URL
     *
     * @return  ?string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set web page or blog URL
     *
     * @param  ?string  $website  Web page or blog URL
     *
     * @return  self
     */
    public function setWebsite(?string $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get preferred e-mail address
     *
     * @return  ?string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set preferred e-mail address
     *
     * @param  ?string  $email  Preferred e-mail address
     *
     * @return  self
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get true if the e-mail address has been verified; otherwise false
     *
     * @return  ?bool
     */
    public function getEmail_verified()
    {
        return $this->email_verified;
    }

    /**
     * Set true if the e-mail address has been verified; otherwise false
     *
     * @param  ?bool  $email_verified  True if the e-mail address has been verified; otherwise false
     *
     * @return  self
     */
    public function setEmail_verified(?bool $email_verified)
    {
        $this->email_verified = $email_verified;

        return $this;
    }

    /**
     * Get gender
     *
     * @return  ?string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set gender
     *
     * @param  ?string  $gender  Gender
     *
     * @return  self
     */
    public function setGender(?string $gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return  ?string
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set birthday
     *
     * @param  ?string  $birthdate  Birthday
     *
     * @return  self
     */
    public function setBirthdate(?string $birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get time zone
     *
     * @return  ?string
     */
    public function getZoneinfo()
    {
        return $this->zoneinfo;
    }

    /**
     * Set time zone
     *
     * @param  ?string  $zoneinfo  Time zone
     *
     * @return  self
     */
    public function setZoneinfo(?string $zoneinfo)
    {
        $this->zoneinfo = $zoneinfo;

        return $this;
    }

    /**
     * Get locale
     *
     * @return  ?string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale
     *
     * @param  ?string  $locale  Locale
     *
     * @return  self
     */
    public function setLocale(?string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get preferred telephone number
     *
     * @return  ?string
     */
    public function getPhone_number()
    {
        return $this->phone_number;
    }

    /**
     * Set preferred telephone number
     *
     * @param  ?string  $phone_number  Preferred telephone number
     *
     * @return  self
     */
    public function setPhone_number(?string $phone_number)
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    /**
     * Get true if the phone number has been verified; otherwise false
     *
     * @return  ?bool
     */
    public function getPhone_number_verified()
    {
        return $this->phone_number_verified;
    }

    /**
     * Set true if the phone number has been verified; otherwise false
     *
     * @param  ?bool  $phone_number_verified  True if the phone number has been verified; otherwise false
     *
     * @return  self
     */
    public function setPhone_number_verified(?bool $phone_number_verified)
    {
        $this->phone_number_verified = $phone_number_verified;

        return $this;
    }

    /**
     * Get preferred postal address
     *
     * @return  ?string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set preferred postal address
     *
     * @param  ?string  $address  Preferred postal address
     *
     * @return  self
     */
    public function setAddress(?string $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get time the information was last updated
     *
     * @return  ?DateTime
     */
    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    /**
     * Set time the information was last updated
     *
     * @param  ?DateTime  $updated_at  Time the information was last updated
     *
     * @return  self
     */
    public function setUpdated_at(?DateTime $updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Get value used to associate a Client session with an ID Token (MAY also be used for nonce values in other applications of JWTs)
     *
     * @return  ?string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * Set value used to associate a Client session with an ID Token (MAY also be used for nonce values in other applications of JWTs)
     *
     * @param  ?string  $nonce  Value used to associate a Client session with an ID Token (MAY also be used for nonce values in other applications of JWTs)
     *
     * @return  self
     */
    public function setNonce(?string $nonce)
    {
        $this->nonce = $nonce;

        return $this;
    }

    /**
     * Get time when the authentication occurred
     *
     * @return  ?DateTime
     */
    public function getAuth_time()
    {
        return $this->auth_time;
    }

    /**
     * Set time when the authentication occurred
     *
     * @param  ?DateTime  $auth_time  Time when the authentication occurred
     *
     * @return  self
     */
    public function setAuth_time(?DateTime $auth_time)
    {
        $this->auth_time = $auth_time;

        return $this;
    }

    /**
     * Get session ID
     *
     * @return  ?string
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set session ID
     *
     * @param  ?string  $sid  Session ID
     *
     * @return  self
     */
    public function setSid(?string $sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get scope Values (example "scope":"email profile phone address")
     *
     * @return  ?string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set scope Values (example "scope":"email profile phone address")
     *
     * @param  ?string  $scope  Scope Values (example "scope":"email profile phone address")
     *
     * @return  self
     */
    public function setScope(?string $scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get the client_id claim carries the client identifier of the OAuth 2.0 [RFC6749] client that requested the token
     *
     * @return  ?string
     */
    public function getClient_id()
    {
        return $this->client_id;
    }

    /**
     * Set the client_id claim carries the client identifier of the OAuth 2.0 [RFC6749] client that requested the token
     *
     * @param  ?string  $client_id  The client_id claim carries the client identifier of the OAuth 2.0 [RFC6749] client that requested the token
     *
     * @return  self
     */
    public function setClient_id(?string $client_id)
    {
        $this->client_id = $client_id;

        return $this;
    }

    /**
     * Get internal clocks.
     *
     * @return  ?int
     */
    public function getExi()
    {
        return $this->exi;
    }

    /**
     * Set internal clocks.
     *
     * @param  ?int  $exi  internal clocks.
     *
     * @return  self
     */
    public function setExi(?int $exi)
    {
        $this->exi = $exi;

        return $this;
    }

    /**
     * Get [string]
     *
     * @return  ?array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set [string]
     *
     * @param  ?array  $roles  [string]
     *
     * @return  self
     */
    public function setRoles(?array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get [string]
     *
     * @return  ?array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set [string]
     *
     * @param  ?array  $groups  [string]
     *
     * @return  self
     */
    public function setGroups(?array $groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get [string]
     *
     * @return  ?array
     */
    public function getEntitlements()
    {
        return $this->entitlements;
    }

    /**
     * Set [string]
     *
     * @param  ?array  $entitlements  [string]
     *
     * @return  self
     */
    public function setEntitlements(?array $entitlements)
    {
        $this->entitlements = $entitlements;

        return $this;
    }

    /**
     * Get the geographic location (TEMPORARY - registered 2022-03-23, extension registered 2023-02-13, expires 2024-03-23)	
     *
     * @return  ?string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the geographic location (TEMPORARY - registered 2022-03-23, extension registered 2023-02-13, expires 2024-03-23)	
     *
     * @param  ?string  $location  The geographic location (TEMPORARY - registered 2022-03-23, extension registered 2023-02-13, expires 2024-03-23)	
     *
     * @return  self
     */
    public function setLocation(?string $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get a structured Claim representing the End-User's place of birth.	
     *
     * @return  ?string
     */
    public function getPlace_of_birth()
    {
        return $this->place_of_birth;
    }

    /**
     * Set a structured Claim representing the End-User's place of birth.	
     *
     * @param  ?string  $place_of_birth  A structured Claim representing the End-User's place of birth.	
     *
     * @return  self
     */
    public function setPlace_of_birth(?string $place_of_birth)
    {
        $this->place_of_birth = $place_of_birth;

        return $this;
    }

    /**
     * Get string array representing the End-User's nationalities.
     *
     * @return  ?string
     */
    public function getNationalities()
    {
        return $this->nationalities;
    }

    /**
     * Set string array representing the End-User's nationalities.
     *
     * @param  ?string  $nationalities  String array representing the End-User's nationalities.
     *
     * @return  self
     */
    public function setNationalities(?string $nationalities)
    {
        $this->nationalities = $nationalities;

        return $this;
    }

    /**
     * Get family name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the family name(s) later in life for any reason. Note that in some cultures, people can have multiple family names or no family name; all can be present, with the names being separated by space characters.
     *
     * @return  ?string
     */
    public function getBirth_family_name()
    {
        return $this->birth_family_name;
    }

    /**
     * Set family name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the family name(s) later in life for any reason. Note that in some cultures, people can have multiple family names or no family name; all can be present, with the names being separated by space characters.
     *
     * @param  ?string  $birth_family_name  Family name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the family name(s) later in life for any reason. Note that in some cultures, people can have multiple family names or no family name; all can be present, with the names being separated by space characters.
     *
     * @return  self
     */
    public function setBirth_family_name(?string $birth_family_name)
    {
        $this->birth_family_name = $birth_family_name;

        return $this;
    }

    /**
     * Get given name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the given name later in life for any reason. Note that in some cultures, people can have multiple given names; all can be present, with the names being separated by space characters.
     *
     * @return  ?string
     */
    public function getBirth_given_name()
    {
        return $this->birth_given_name;
    }

    /**
     * Set given name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the given name later in life for any reason. Note that in some cultures, people can have multiple given names; all can be present, with the names being separated by space characters.
     *
     * @param  ?string  $birth_given_name  Given name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the given name later in life for any reason. Note that in some cultures, people can have multiple given names; all can be present, with the names being separated by space characters.
     *
     * @return  self
     */
    public function setBirth_given_name(?string $birth_given_name)
    {
        $this->birth_given_name = $birth_given_name;

        return $this;
    }

    /**
     * Get middle name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the middle name later in life for any reason. Note that in some cultures, people can have multiple middle names; all can be present, with the names being separated by space characters. Also note that in some cultures, middle names are not used.
     *
     * @return  ?string
     */
    public function getBirth_middle_name()
    {
        return $this->birth_middle_name;
    }

    /**
     * Set middle name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the middle name later in life for any reason. Note that in some cultures, people can have multiple middle names; all can be present, with the names being separated by space characters. Also note that in some cultures, middle names are not used.
     *
     * @param  ?string  $birth_middle_name  Middle name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the middle name later in life for any reason. Note that in some cultures, people can have multiple middle names; all can be present, with the names being separated by space characters. Also note that in some cultures, middle names are not used.
     *
     * @return  self
     */
    public function setBirth_middle_name(?string $birth_middle_name)
    {
        $this->birth_middle_name = $birth_middle_name;

        return $this;
    }

    /**
     * Get end-User's salutation, e.g., "Mr."
     *
     * @return  ?string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Set end-User's salutation, e.g., "Mr."
     *
     * @param  ?string  $salutation  End-User's salutation, e.g., "Mr."
     *
     * @return  self
     */
    public function setSalutation(?string $salutation)
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * Get end-User's title, e.g., "Dr."
     *
     * @return  ?string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set end-User's title, e.g., "Dr."
     *
     * @param  ?string  $title  End-User's title, e.g., "Dr."
     *
     * @return  self
     */
    public function setTitle(?string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get end-User's mobile phone number formatted according to ITU-T recommendation
     *
     * @return  ?string
     */
    public function getMsisdn()
    {
        return $this->msisdn;
    }

    /**
     * Set end-User's mobile phone number formatted according to ITU-T recommendation
     *
     * @param  ?string  $msisdn  End-User's mobile phone number formatted according to ITU-T recommendation
     *
     * @return  self
     */
    public function setMsisdn(?string $msisdn)
    {
        $this->msisdn = $msisdn;

        return $this;
    }

    /**
     * Get stage name, religious name or any other type of alias/pseudonym with which a person is known in a specific context besides its legal name. This must be part of the applicable legislation and thus the trust framework (e.g., be an attribute on the identity card).
     *
     * @return  ?string
     */
    public function getAlso_known_as()
    {
        return $this->also_known_as;
    }

    /**
     * Set stage name, religious name or any other type of alias/pseudonym with which a person is known in a specific context besides its legal name. This must be part of the applicable legislation and thus the trust framework (e.g., be an attribute on the identity card).
     *
     * @param  ?string  $also_known_as  Stage name, religious name or any other type of alias/pseudonym with which a person is known in a specific context besides its legal name. This must be part of the applicable legislation and thus the trust framework (e.g., be an attribute on the identity card).
     *
     * @return  self
     */
    public function setAlso_known_as(?string $also_known_as)
    {
        $this->also_known_as = $also_known_as;

        return $this;
    }
}
