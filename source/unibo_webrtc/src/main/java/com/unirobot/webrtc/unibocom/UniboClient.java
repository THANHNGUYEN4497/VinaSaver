package com.unirobot.webrtc.unibocom;

import android.content.Context;

import com.unirobot.webrtc.unibocom.callback.CallListener;
import com.unirobot.webrtc.unibocom.callback.ConnectionListener;
import com.unirobot.webrtc.unibocom.callback.EventListener;
import com.unirobot.webrtc.unibocom.callback.RoomListener;
import com.unirobot.webrtc.unibocom.client.RoomHandler;
import com.unirobot.webrtc.unibocom.client.WebRTCHandler;
import com.unirobot.webrtc.unibocom.client.object.PeerSession;
import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;
import com.unirobot.webrtc.unibocom.configuration.StreamConfiguration;
import com.unirobot.webrtc.unibocom.control.UniboProxyRenderer;
import com.unirobot.webrtc.unibocom.control.UniboVideoView;
import com.unirobot.webrtc.unibocom.signaling.SignalHandler;
import com.unirobot.webrtc.unibocom.signaling.SocketHandler;
import com.unirobot.webrtc.unibocom.signaling.callback.SignalingHandlerDelegate;
import com.unirobot.webrtc.unibocom.signaling.callback.SocketHandlerDelegate;
import com.unirobot.webrtc.unibocom.signaling.callback.WebRTCHandlerDelegate;

import org.json.JSONException;
import org.json.JSONObject;
import org.webrtc.EglBase;
import org.webrtc.RendererCommon;

import java.util.List;

/**
 * Created by San Vo on 27/02/2018.
 */

public class UniboClient implements IUnibo, SocketHandlerDelegate, SignalingHandlerDelegate, WebRTCHandlerDelegate {

    public void setVideoMaxBitrate(int videoMaxBitrate) {
        _WebRTCHandler.setVideoMaxBitrate(videoMaxBitrate);
    }

    public enum RenderScale {ASPECT_FILL, ASPECT_FIT, ASPECT_BALANCED}

    private static UniboClient _instance;

    private SocketHandler _socketHandler;
    private SignalHandler _signalHandler;
    private WebRTCHandler _WebRTCHandler;
    private RoomHandler _roomHandler;

    private EventListener _eventListener;
    private RoomListener _roomListener;
    private CallListener _callListener;
    private ConnectionListener _connectionListener;

    private EglBase _rootEglBase;

    private boolean _isPrepareDone;

    private UniboClient() {
        _roomHandler = new RoomHandler();
        _socketHandler = new SocketHandler(this);
        _signalHandler = new SignalHandler(this, _socketHandler, _roomHandler);
        _WebRTCHandler = new WebRTCHandler(this, _signalHandler);

        _isPrepareDone = false;
    }

    public static UniboClient getInstance() {
        if (_instance == null)
            _instance = new UniboClient();

        return _instance;
    }

    @Override
    public void configure(String signalingUrl, List<String> stunUrls, StreamConfiguration streamConfiguration) {
        _socketHandler.setSocketUrl(signalingUrl);
        _WebRTCHandler.setStunUrls(stunUrls);
        _WebRTCHandler.setStreamConfiguration(streamConfiguration);
    }

    @Override
    public void prepare(Context context) {
        if (_isPrepareDone)
            return;

        if (_rootEglBase == null)
            _rootEglBase = EglBase.create();

        _WebRTCHandler.createPeerConnectionFactoryInternal(context, _rootEglBase);
        _WebRTCHandler.initLocalMediaSource();

        _isPrepareDone = true;
    }

    @Override
    public void connect() {
        if (!_isPrepareDone) {
            if (_connectionListener != null)
                _connectionListener.onConnectFail(MessageConst.unibo_not_prepare_yet);
        }

        try {
            if (!_socketHandler.connect(_connectionListener)) {
                if (_connectionListener != null)
                    _connectionListener.onConnectFail(MessageConst.signaling_connect_no_url);
            }
        } catch (Exception e) {
            if (_connectionListener != null)
                _connectionListener.onConnectFail(e.getMessage());
        }
    }

    @Override
    public void disconnect() {
        this.hangupAll();

        try {
            _roomHandler.clearAll();
            _socketHandler.disconnect();
            _WebRTCHandler.close();

        } catch (InterruptedException e) {
            if (_connectionListener != null)
                _connectionListener.onDisconnectFail(e.getMessage());
        }
    }

    @Override
    public boolean isConnected() {
        return _socketHandler.isConnected();
    }

    @Override
    public void joinRoom(String roomName, String nickName, String uid) {
        if (roomName == null) {
            if (_roomListener != null)
                _roomListener.onJoinRoomFail("", MessageConst.room_no_name);
            return;
        }

        if (!isConnected() || _socketHandler.getMySocketId() == null) {
            if (_roomListener != null)
                _roomListener.onJoinRoomFail(roomName, MessageConst.webrtc_not_connected);
            return;
        }

        try {
            User myUser = new User(nickName, _socketHandler.getMySocketId(), uid);

            if (_roomHandler.createRoom(roomName, myUser)) {
                _signalHandler.joinRoom(roomName, myUser);
            } else {
                if (_roomListener != null)
                    _roomListener.onJoinRoomFail(roomName, MessageConst.room_joined);
            }
        } catch (JSONException e) {
            if (_eventListener != null)
                _eventListener.onRoomEventError(roomName, e.getMessage());
        }
    }

    @Override
    public void leaveRoom(String roomName) {
        this.hangupAll();

        if (roomName == null) {
            if (_roomListener != null)
                _roomListener.onLeaveRoomFail("", MessageConst.room_no_name);
            return;
        }

        if (!isConnected()) {
            _roomListener.onLeaveRoomFail(roomName, MessageConst.webrtc_not_connected);
            return;
        }
        _signalHandler.leaveRoom(roomName);
    }

    @Override
    public void configureVideoView(UniboVideoView videoView, RenderScale renderScale, boolean isOnTop) {
        if (_rootEglBase == null)
            _rootEglBase = EglBase.create();

        videoView.init(_rootEglBase.getEglBaseContext(), null);

        switch (renderScale) {
            case ASPECT_FILL:
                videoView.setScalingType(RendererCommon.ScalingType.SCALE_ASPECT_FILL);
                break;
            case ASPECT_FIT:
                videoView.setScalingType(RendererCommon.ScalingType.SCALE_ASPECT_FIT);
                break;
            case ASPECT_BALANCED:
                videoView.setScalingType(RendererCommon.ScalingType.SCALE_ASPECT_BALANCED);
                break;
        }

        videoView.setEnableHardwareScaler(true);
        if (isOnTop)
            videoView.setZOrderMediaOverlay(true);
    }

    @Override
    public void startLocalMediaSource() {
        if (_isPrepareDone) {
            _WebRTCHandler.startVideoSource();
        }
    }

    @Override
    public void stopLocalMediaSource() {
        if (_isPrepareDone) {
            _WebRTCHandler.stopVideoSource();
        }
    }

    @Override
    public void call(Room room, User partner) {
        if (!_isPrepareDone || !isConnected()) {
            _callListener.onCallFail(room, partner, MessageConst.webrtc_not_prepared);
            return;
        }

        try {
            _signalHandler.call(room.getRoomName(), partner.getUid());
        } catch (JSONException e) {
            if (_callListener != null)
                _eventListener.onRoomEventError(room.getRoomName(), e.getMessage());
        }
    }

    @Override
    public void response(Room room, User caller) {
        try {
            _signalHandler.response(room.getRoomName(), caller.getUid());
        } catch (JSONException e) {
            if (_eventListener != null)
                _eventListener.onRoomEventError(room.getRoomName(), e.getMessage());
        }
    }

    @Override
    public void hangup(Room room, User partner) {
        if (!isConnected()) {
            _callListener.onHangupFail(room, partner);
            return;
        }

        _WebRTCHandler.closePeerConnection();
        try {
            _signalHandler.hangup(room.getRoomName(), partner.getUid());
        } catch (JSONException e) {
            if (_eventListener != null)
                _eventListener.onRoomEventError(room.getRoomName(), e.getMessage());
        }
    }

    @Override
    public void hangupAll() {
        PeerSession peerSession = _WebRTCHandler.getPeerSession();
        if (peerSession != null)
            hangup(peerSession.getRoom(), peerSession.getTargetUser());
    }

    @Override
    public void setAudioEnabled(boolean enable) {
        _WebRTCHandler.setAudioEnabled(enable);
    }

    @Override
    public void setVideoEnabled(boolean enable) {
        _WebRTCHandler.setVideoEnabled(enable);
    }

    @Override
    public void switchCamera() {
        _WebRTCHandler.switchCamera();
    }

    @Override
    public void setConnectionDelegate(ConnectionListener delegate) {
        _connectionListener = delegate;
    }

    @Override
    public void setCallDelegate(CallListener delegate) {
        _callListener = delegate;
    }

    @Override
    public void setEventDelegate(EventListener eventListener) {
        _eventListener = eventListener;
        _socketHandler.setEventDelegate(eventListener);
    }

    @Override
    public void setRoomDelegate(RoomListener roomListener) {
        _roomListener = roomListener;
        _roomHandler.setRoomDelegate(_roomListener);
    }

    @Override
    public void setLocalRenderProxy(UniboProxyRenderer localRenderProxy) {
        _WebRTCHandler.setLocalRenderProxy(localRenderProxy);
    }

    @Override
    public void setRemoteRenderProxy(Room room, User targetUser, UniboProxyRenderer remoteRenderProxy) {
        if (!_WebRTCHandler.setRemoteRenderProxy(room, targetUser, remoteRenderProxy)) {
            if (_callListener != null)
                _callListener.setRemoteProxyFail(MessageConst.control_set_remote_proxy_fail);
        }
    }

    @Override
    public void release() {
        if (_rootEglBase != null) {
            _rootEglBase.release();
            _rootEglBase = null;
        }

        try {
            _WebRTCHandler.release();
        } catch (InterruptedException e) {
            if (_eventListener != null)
                _eventListener.onEventError(e.getMessage());
        }

        _isPrepareDone = false;
    }

    //------------SocketHandlerDelegate
    @Override
    public void onReceiveSocketMessage(Object msg) {
        try {
            _signalHandler.handleMessage((JSONObject) msg);
        } catch (JSONException e) {
            if (_eventListener != null)
                _eventListener.onEventError(e.getMessage());
        }
    }

    @Override
    public void onWebsocketError(String errMsg) {
        if (_eventListener != null)
            _eventListener.onEventError(errMsg);
        disconnect();
    }

    //------------SignalingHandlerDelegate
    @Override
    public void onBeCalled(Room room, User caller) {
        if (_callListener != null)
            _callListener.onBeCalled(room, caller);
    }

    @Override
    public void onBeAccepted(Room room, User acceptor) {
        if (_callListener != null)
            _callListener.onBeAccepted(room, acceptor);

        _WebRTCHandler.connectTo(room, acceptor, true);
        _WebRTCHandler.createOffer();
    }

    @Override
    public void onCallingFail(Room room, User partner, String msg) {
        if (_callListener != null) {
            _callListener.onCallFail(room, partner, msg);
        }
    }

    @Override
    public void onReceiveOffer(Room room, User caller, String sdp) {
        _WebRTCHandler.connectTo(room, caller, false);
        _WebRTCHandler.createAnswer(room, caller, sdp);
    }

    @Override
    public void onReceiveAnswer(Room room, User acceptor, String sdp) {
        _WebRTCHandler.setAnswer(room, acceptor, sdp);
    }

    @Override
    public void onReceiveCandidate(Room room, User acceptor, int sdpMLineIndex, String sdpMid, String candidate) {
        _WebRTCHandler.addRemoteIceCandidate(room, acceptor, sdpMLineIndex, sdpMid, candidate);
    }

    @Override
    public void onReceiveHangup(Room room, User partner) {
        _WebRTCHandler.closePeerConnection();
        if (_callListener != null)
            _callListener.onHungup(room, partner);
    }

    @Override
    public void onJoinRoomFail(String roomName, String msg) {
        if (_roomListener != null) {
            _roomListener.onJoinRoomFail(roomName, msg);
        }
    }

    @Override
    public void onLeaveRoomFail(String roomName, String msg) {
        if (_roomListener != null) {
            _roomListener.onLeaveRoomFail(roomName, msg);
        }
    }

    @Override
    public void onErrorMessage(String roomName, String msg) {
        if (_callListener != null) {
            _eventListener.onRoomEventError(roomName, msg);
        }
    }

    //------------WebRTCHandlerDelegate

    @Override
    public void onWebRTCHandlerError(String errorMsg) {
        if (_eventListener != null)
            _eventListener.onEventError(errorMsg);
    }

    @Override
    public void onIceConnected(Room room, User partner) {
        if (_callListener != null)
            _callListener.onCallSuccess(room, partner);
    }

    @Override
    public void onIceClosed(Room room, User partner) {
        if (_callListener != null)
            _callListener.onHangupSuccess(room, partner);
    }
}
