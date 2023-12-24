<?php

namespace GPDAuth\Graphql;

use DateTime;
use GPDAuth\Entities\User;
use GraphQL\Type\Definition\Type;
use GPDCore\Graphql\Types\DateType;
use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\ObjectType;

class TypeFactoryAuthSession
{

    const NAME = 'AuthSession';

    public static function create(IContextService $contxt, string $name = TypeFactoryAuthSession::NAME, $description = '')
    {
        $types = $contxt->getTypes();
        $serviceManager = $contxt->getServiceManager();
        return new ObjectType([
            'name' => $name,
            'description' => $description,
            'fields' => [
                'iss' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'Issuer'
                ],
                'sub' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'Subject (username) identifies the principal that is the subject of the JWT'
                ],
                'aud' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'Audience (allowed clients)'
                ],
                'exp' => [
                    'type' => $serviceManager->get(DateTime::class),
                    'description' => 'Expiration Time'
                ],
                'nbf' => [
                    'type' => $serviceManager->get(DateTime::class),
                    'description' => 'Not Before'
                ],
                'iat' => [
                    'type' => $serviceManager->get(DateTime::class),
                    'description' => 'Issued At'
                ],
                'jti' => [
                    'type' => Type::string(),
                    'description' => 'JWT ID'
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'Full name'
                ],
                'given_name' => [
                    'type' => Type::string(),
                    'description' => 'Given name(s) or first name(s)'
                ],

                'family_name' => [
                    'type' => Type::string(),
                    'description' => 'Surname(s) or last name(s)'
                ],
                'middle_name' => [
                    'type' => Type::string(),
                    'description' => 'Middle name(s)'
                ],
                'nickname' => [
                    'type' => Type::string(),
                    'description' => 'Casual name'
                ],
                'preferred_username' => [
                    'type' => Type::string(),
                    'description' => 'Shorthand name by which the End-User wishes to be referred to'
                ],
                'profile' => [
                    'type' => Type::string(),
                    'description' => ''
                ],
                'picture' => [
                    'type' => Type::string(),
                    'description' => 'Profile picture URL'
                ],
                'website' => [
                    'type' => Type::string(),
                    'description' => 'Web page or blog URL'
                ],
                'email' => [
                    'type' => Type::string(),
                    'description' => 'Preferred e-mail address'
                ],
                'email_verified' => [
                    'type' => Type::boolean(),
                    'description' => 'True if the e-mail address has been verified; otherwise false'
                ],
                'gender' => [
                    'type' => Type::string(),
                    'description' => 'Gender'
                ],
                'birthdate' => [
                    'type' => $serviceManager->get(DateType::class),
                    'description' => 'Birthday'
                ],
                'zoneinfo' => [
                    'type' => Type::string(),
                    'description' => 'Time zone'
                ],
                'locale' => [
                    'type' => Type::string(),
                    'description' => 'Locale'
                ],
                'phone_number' => [
                    'type' => Type::string(),
                    'description' => 'Preferred telephone number'
                ],
                'phone_number_verified' => [
                    'type' => Type::boolean(),
                    'description' => 'True if the phone number has been verified; otherwise false'
                ],
                'address' => [
                    'type' => Type::string(),
                    'description' => 'Preferred postal address'
                ],
                'updated_at' => [
                    'type' => $serviceManager->get(DateTime::class),
                    'description' => 'Time the information was last updated'
                ],
                'nonce' => [
                    'type' => Type::string(),
                    'description' => 'Value used to associate a Client session with an ID Token (MAY also be used for nonce values in other applications of JWTs)'
                ],
                'auth_time' => [
                    'type' => $serviceManager->get(DateTime::class),
                    'description' => 'Time when the authentication occurred'
                ],
                'sid' => [
                    'type' => Type::string(),
                    'description' => 'Session ID'
                ],
                'scope' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'Scope Values (example  "scope":"email profile phone address")'
                ],
                'client_id' => [
                    'type' => Type::string(),
                    'description' => 'The client_id claim carries the client identifier of the OAuth 2.0 [RFC6749] client that requested the token'
                ],
                'exi' => [
                    'type' => Type::int(),
                    'description' => 'Expires in  Lifetime of the token in seconds from the time the RS first sees it.  Used to implement a weaker from of token expiration for devices that cannot synchronize their internal clocks.'
                ],
                'roles' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'A list of roles for the user that collectively represent who the user is, e.g., "Student", "Faculty".  No vocabulary or syntax is specified, although it is expected that a role value is a String or label representing a collection of entitlements.  This value has no canonical types.'
                ],
                'groups' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'A list of groups to which the user belongs, either through direct membership, through nested groups, or dynamically calculated.  The values are meant to enable expression of common group-based or role-based access control models, although no explicit authorization model is defined.  It is intended that the semantics of group membership and any behavior or authorization granted as a result of membership are defined by the service provider.  The canonical types "direct" and "indirect" are defined to describe how the group membership was derived.  Direct group membership indicates that the user is directly associated with the group and SHOULD indicate that clients may modify membership through the "Group" resource.  Indirect membership indicates that user membership is transitive or dynamic and implies that clients cannot modify indirect group membership through the "Group" resource but MAY modify direct group membership through the "Group" resource, which may influence indirect memberships.  If the SCIM service provider exposes a "Group" resource, the "value"'
                ],
                'entitlements' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'A list of entitlements for the user that represent a thing the user has.  An entitlement may be an additional right to a thing, object, or service.  No vocabulary or syntax is specified; service providers and clients are expected to encode sufficient information in the value so as to accurately and without ambiguity determine what the user has access to.  This value has no canonical types, although a type may be useful as a means to scope entitlements.'
                ],
                'location' => [
                    'type' => Type::string(),
                    'description' => 'The geographic location (TEMPORARY - registered 2022-03-23, extension registered 2023-02-13, expires 2024-03-23)	'
                ],
                'place_of_birth' => [
                    'type' => Type::string(),
                    'description' => "A structured Claim representing the End-User's place of birth"
                ],
                'nationalities' => [
                    'type' => Type::string(),
                    'description' => "String array representing the End-User's nationalities."
                ],
                'birth_family_name' => [
                    'type' => Type::string(),
                    'description' => 'Family name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the family name(s) later in life for any reason. Note that in some cultures, people can have multiple family names or no family name; all can be present, with the names being separated by space characters.'
                ],
                'birth_given_name' => [
                    'type' => Type::string(),
                    'description' => 'Given name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the given name later in life for any reason. Note that in some cultures, people can have multiple given names; all can be present, with the names being separated by space characters'
                ],
                'birth_middle_name' => [
                    'type' => Type::string(),
                    'description' => 'Middle name(s) someone has when they were born, or at least from the time they were a child. This term can be used by a person who changes the middle name later in life for any reason. Note that in some cultures, people can have multiple middle names; all can be present, with the names being separated by space characters. Also note that in some cultures, middle names are not used.'
                ],
                'salutation' => [
                    'type' => Type::string(),
                    'description' => 'End-User\'s salutation, e.g., "Mr."'
                ],
                'title' => [
                    'type' => Type::string(),
                    'description' => 'End-User\'s title, e.g., "Dr."'
                ],
                'msisdn' => [
                    'type' => Type::string(),
                    'description' => 'End-User\'s mobile phone number formatted according to ITU-T recommendation'
                ],
                'also_known_as' => [
                    'type' => Type::string(),
                    'description' => 'Stage name, religious name or any other type of alias/pseudonym with which a person is known in a specific context besides its legal name. This must be part of the applicable legislation and thus the trust framework (e.g., be an attribute on the identity card).'
                ],
            ]
        ]);
    }
}
