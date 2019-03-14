package com.unirobot.webrtc.unibocom.signaling;

import com.unirobot.webrtc.unibocom.callback.ConnectionListener;
import com.unirobot.webrtc.unibocom.callback.EventListener;
import com.unirobot.webrtc.unibocom.signaling.callback.SocketHandlerDelegate;

import java.net.URISyntaxException;
import java.security.KeyManagementException;
import java.security.NoSuchAlgorithmException;
import java.security.cert.CertificateException;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

import io.socket.client.IO;
import io.socket.client.Socket;
import io.socket.emitter.Emitter;
import okhttp3.OkHttpClient;

/**
 * Created by San Vo on 27/02/2018.
 */

public class SocketHandler {
    private Socket _clientSocket;
    private String _socketUrl;

    private SocketHandlerDelegate _socketHandlerDelegate;

    private ConnectionListener _connectionListener;
    private EventListener _eventListener;

    private boolean _isConnected;

    public SocketHandler(SocketHandlerDelegate socketHandlerDelegate) {
        _socketHandlerDelegate = socketHandlerDelegate;
        _isConnected = false;
    }

    public void setSocketUrl(String socketUrl) {
        _socketUrl = socketUrl;
    }

    public boolean connect(ConnectionListener delegate) throws URISyntaxException, KeyManagementException, NoSuchAlgorithmException {
        if(_socketUrl==null) return false;

        if ( _clientSocket != null && _clientSocket.connected()) {
            disconnect();
        }

        _connectionListener = delegate;

        initializeSocket(_socketUrl);

        _clientSocket.connect();

        listenEvents();

        return true;
    }

    public void disconnect() {
        if(_clientSocket != null) {
            _clientSocket.disconnect();
            _clientSocket.close();
            _clientSocket = null;
            _isConnected = false;
        }
    }

    public boolean isConnected() {
        return _isConnected;
    }

    public void setEventDelegate(EventListener eventListener) {
        _eventListener = eventListener;
    }

    public void emit(String event, Object... args) {
        if(_isConnected && _clientSocket != null)
            _clientSocket.emit(event,args);
    }

    public String getMySocketId() {
        if(_isConnected && _clientSocket != null)
            return _clientSocket.id();
        return null;
    }

    private void initializeSocket(String socketUrl) throws URISyntaxException, KeyManagementException, NoSuchAlgorithmException {
        if(socketUrl.startsWith("https")) {
            final TrustManager[] trustAllCerts = new TrustManager[] {
                    new X509TrustManager() {
                        @Override
                        public void checkClientTrusted(java.security.cert.X509Certificate[] chain, String authType) throws CertificateException {
                        }

                        @Override
                        public void checkServerTrusted(java.security.cert.X509Certificate[] chain, String authType) throws CertificateException {
                        }

                        @Override
                        public java.security.cert.X509Certificate[] getAcceptedIssuers() {
                            return new java.security.cert.X509Certificate[]{};
                        }
                    }
            };
            final SSLContext sslContext = SSLContext.getInstance("SSL");
            sslContext.init(null, trustAllCerts, new java.security.SecureRandom());
            OkHttpClient okHttpClient = new OkHttpClient.Builder()
                    .hostnameVerifier(new HostnameVerifier() {
                        @Override
                        public boolean verify(String hostname, SSLSession session) {
                            return true;
                        }
                    })
                    .sslSocketFactory(sslContext.getSocketFactory(), (X509TrustManager)trustAllCerts[0])
                    .build();
            IO.setDefaultOkHttpWebSocketFactory(okHttpClient);
            IO.setDefaultOkHttpCallFactory(okHttpClient);
            IO.Options opts = new IO.Options();
            opts.callFactory = okHttpClient;
            opts.webSocketFactory = okHttpClient;
            _clientSocket = IO.socket( _socketUrl, opts);
        }
        else {
            _clientSocket = IO.socket( _socketUrl);
        }
    }

    private void listenEvents() {
        _clientSocket.on(Socket.EVENT_CONNECT, new Emitter.Listener() {
            @Override
            public void call(Object... args) {
                _isConnected = true;
                if(_connectionListener != null)
                    _connectionListener.onConnectSuccess();

            }
        }).on(Socket.EVENT_DISCONNECT, new Emitter.Listener() {
            @Override
            public void call(Object... args) {
                _isConnected = false;
                if(_connectionListener != null)
                    _connectionListener.onDisconnected();
            }
        }).on(Socket.EVENT_CONNECT_ERROR, new Emitter.Listener() {
            @Override
            public void call(Object... args) {
                if(_connectionListener != null && args != null && args.length > 0)
                    _connectionListener.onConnectFail(((Exception)args[0]).getMessage());
            }
        }).on(Socket.EVENT_MESSAGE, new Emitter.Listener() {
            @Override
            public void call(Object... args) {
                if(args != null && args.length > 0 && _socketHandlerDelegate != null) {
                    _socketHandlerDelegate.onReceiveSocketMessage(args[0]);
                }
            }

        }).on(Socket.EVENT_ERROR, new Emitter.Listener() {
            @Override
            public void call(Object... args) {
                if(_socketHandlerDelegate != null && args != null && args.length > 0)
                    _socketHandlerDelegate.onWebsocketError(((Exception)args[0]).getMessage());
            }
        });
    }
}
