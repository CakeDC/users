//Source code based on https://github.com/web-auth/webauthn-helper
// Predefined fetch function
const fetchEndpoint = (data, url, header) => {
    return fetch(
        url,
        {
            method: 'POST',
            credentials: 'same-origin',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...header
            },
            body: JSON.stringify(data),
        }
    );
}

// Decodes a Base64Url string
const base64UrlDecode = (input) => {
    input = input
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const pad = input.length % 4;
    if (pad) {
        if (pad === 1) {
            throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding');
        }
        input += new Array(5-pad).join('=');
    }

    return window.atob(input);
};

// Converts an array of bytes into a Base64Url string
const arrayToBase64String = (a) => btoa(String.fromCharCode(...a));

// Prepares the public key options object returned by the Webauthn Framework
const preparePublicKeyOptions = publicKey => {
    //Convert challenge from Base64Url string to Uint8Array
    publicKey.challenge = Uint8Array.from(
        base64UrlDecode(publicKey.challenge),
        c => c.charCodeAt(0)
    );

    //Convert the user ID from Base64 string to Uint8Array
    if (publicKey.user !== undefined) {
        publicKey.user = {
            ...publicKey.user,
            id: Uint8Array.from(
                window.atob(publicKey.user.id),
                c => c.charCodeAt(0)
            ),
        };
    }

    //If excludeCredentials is defined, we convert all IDs to Uint8Array
    if (publicKey.excludeCredentials !== undefined) {
        publicKey.excludeCredentials = publicKey.excludeCredentials.map(
            data => {
                return {
                    ...data,
                    id: Uint8Array.from(
                        base64UrlDecode(data.id),
                        c => c.charCodeAt(0)
                    ),
                };
            }
        );
    }

    if (publicKey.allowCredentials !== undefined) {
        publicKey.allowCredentials = publicKey.allowCredentials.map(
            data => {
                return {
                    ...data,
                    id: Uint8Array.from(
                        base64UrlDecode(data.id),
                        c => c.charCodeAt(0)
                    ),
                };
            }
        );
    }

    return publicKey;
};

// Prepares the public key credentials object returned by the authenticator
const preparePublicKeyCredentials = data => {
    const publicKeyCredential = {
        id: data.id,
        type: data.type,
        rawId: arrayToBase64String(new Uint8Array(data.rawId)),
        response: {
            clientDataJSON: arrayToBase64String(
                new Uint8Array(data.response.clientDataJSON)
            ),
        },
    };

    if (data.response.attestationObject !== undefined) {
        publicKeyCredential.response.attestationObject = arrayToBase64String(
            new Uint8Array(data.response.attestationObject)
        );
    }

    if (data.response.authenticatorData !== undefined) {
        publicKeyCredential.response.authenticatorData = arrayToBase64String(
            new Uint8Array(data.response.authenticatorData)
        );
    }

    if (data.response.signature !== undefined) {
        publicKeyCredential.response.signature = arrayToBase64String(
            new Uint8Array(data.response.signature)
        );
    }

    if (data.response.userHandle !== undefined) {
        publicKeyCredential.response.userHandle = arrayToBase64String(
            new Uint8Array(data.response.userHandle)
        );
    }

    return publicKeyCredential;
};

const useLogin = ({actionUrl = '/login', actionHeader = {}, optionsUrl = '/login/options'}, optionsHeader = {}) => {
    return async (data) => {
        const optionsResponse = await fetchEndpoint(data, optionsUrl, optionsHeader);
        const json = await optionsResponse.json();
        const publicKey = preparePublicKeyOptions(json);
        const credentials = await navigator.credentials.get({publicKey});
        const publicKeyCredential = preparePublicKeyCredentials(credentials);
        const actionResponse = await fetchEndpoint(publicKeyCredential, actionUrl, actionHeader);
        if (! actionResponse.ok) {
            throw actionResponse;
        }
        const responseBody = await actionResponse.text();

        return responseBody !== '' ? JSON.parse(responseBody) : responseBody;
    };
};


const useRegistration = ({actionUrl = '/register', actionHeader = {}, optionsUrl = '/register/options'}, optionsHeader = {}) => {
    return async (data) => {
        const optionsResponse = await fetchEndpoint(data, optionsUrl, optionsHeader);
        const json = await optionsResponse.json();
        const publicKey = preparePublicKeyOptions(json);
        const credentials = await navigator.credentials.create({publicKey});
        const publicKeyCredential = preparePublicKeyCredentials(credentials);
        const actionResponse = await fetchEndpoint(publicKeyCredential, actionUrl, actionHeader);
        if (! actionResponse.ok) {
            throw actionResponse;
        }
        const responseBody = await actionResponse.text();

        return responseBody !== '' ? JSON.parse(responseBody) : responseBody;
    };
};

var Webauthn2faHelper = {
    toggleElem: function(options) {
        console.log({options});
        document.getElementById(options.authenticateElemId).style.display = options.isRegister ? 'none' : 'block';
        document.getElementById(options.registerElemId).style.display = options.isRegister ? 'block' : 'none';
    },
    authenticate: function(options) {
        var authenticate = useLogin({
            actionUrl: options.authenticateActionUrl,
            optionsUrl: options.authenticateOptionsUrl
        });
        authenticate({
                username: options.username
        })
        .then(function (response) {
            window.location.href = response.redirectUrl;
        })
        .catch((error) => {
            window.alert('Authentication failure');
        })
    },
    register: function(options) {
        var register = useRegistration({
            actionUrl: options.registerActionUrl,
            optionsUrl: options.registerOptionsUrl
        });

        return register({
            username: options.username,
        })
            .catch((error) => {
                window.alert('Registration failed');
            });
    },
    run: function(options) {
        Webauthn2faHelper.toggleElem(options);
        if (options.isRegister) {
            return Webauthn2faHelper.register(options)
                .then(function (response) {
                    if (response && response.success) {
                        options.isRegister = false;
                        Webauthn2faHelper.toggleElem(options);
                        Webauthn2faHelper.authenticate(options);
                    }
                });
        }
        return Webauthn2faHelper.authenticate(options);
    }
}
