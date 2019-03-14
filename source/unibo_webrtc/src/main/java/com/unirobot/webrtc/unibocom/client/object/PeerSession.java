package com.unirobot.webrtc.unibocom.client.object;

import android.util.Log;

import com.unirobot.webrtc.unibocom.control.UniboProxyRenderer;

import org.webrtc.IceCandidate;
import org.webrtc.MediaConstraints;
import org.webrtc.MediaStream;
import org.webrtc.PeerConnection;
import org.webrtc.PeerConnectionFactory;
import org.webrtc.RtpSender;
import org.webrtc.SdpObserver;
import org.webrtc.SessionDescription;
import org.webrtc.VideoRenderer;
import org.webrtc.VideoTrack;

import java.util.LinkedList;
import java.util.List;

/**
 * Created by San Vo on 30/03/2018.
 */

public class PeerSession {
    private static final String TAG = "Unibo - PeerSession";

    private static final String DTLS_SRTP_KEY_AGREEMENT_CONSTRAINT = "DtlsSrtpKeyAgreement";
    private static final String VIDEO_TRACK_TYPE = "video";

    private Room _room;
    private User _targetUser;
    private boolean _isCaller;

    private PeerConnection _peerConnection;
    private PeerConnection.Observer _peerConnectionObserver;
    private SdpObserver _sdpObserver;

    private VideoTrack _remoteVideoTrack;
    private UniboProxyRenderer _remoteRenderProxy;

    private LinkedList<IceCandidate> _queuedRemoteCandidates;

    public PeerSession(Room room, User targetUser, boolean isCaller) {
        _room = room;
        _targetUser = targetUser;
        _isCaller = isCaller;
    }

    public Room getRoom() {
        return _room;
    }

    public User getTargetUser() {
        return _targetUser;
    }


    public void clearPeerSession() {
        _peerConnection = null;
        _remoteVideoTrack = null;
        _queuedRemoteCandidates = null;
    }


    public void initPeerConnection(PeerConnectionFactory peerConnectionFactory, List<PeerConnection.IceServer> stunServers) {
        _queuedRemoteCandidates = new LinkedList<>();

        //
        PeerConnection.RTCConfiguration rtcConfig = new PeerConnection.RTCConfiguration(stunServers);
        // TCP candidates are only useful when connecting to a server that supports ICE-TCP.
        rtcConfig.tcpCandidatePolicy = PeerConnection.TcpCandidatePolicy.ENABLED;
        rtcConfig.bundlePolicy = PeerConnection.BundlePolicy.MAXBUNDLE;
        rtcConfig.rtcpMuxPolicy = PeerConnection.RtcpMuxPolicy.REQUIRE;
        rtcConfig.continualGatheringPolicy = PeerConnection.ContinualGatheringPolicy.GATHER_CONTINUALLY;
        // Use ECDSA encryption.
        rtcConfig.keyType = PeerConnection.KeyType.ECDSA;

        // Create peer connection constraints.
        MediaConstraints peerConnectionConstraints = new MediaConstraints();
        // Enable DTLS for normal calls and disable for loopback calls.
        peerConnectionConstraints.optional.add(
                new MediaConstraints.KeyValuePair(DTLS_SRTP_KEY_AGREEMENT_CONSTRAINT, "true"));

        Log.d(TAG, "PCConstraints: " + peerConnectionConstraints.toString());

        // Create peer connection
        _peerConnection = peerConnectionFactory.createPeerConnection(rtcConfig, peerConnectionConstraints, _peerConnectionObserver);
    }

    public void releasePeerConnection(MediaStream localMediaStream) {
        if(_peerConnection != null) {
            _peerConnection.removeStream(localMediaStream);
            _peerConnection.dispose();
            _peerConnection = null;
        }
        _remoteRenderProxy = null;
    }

    public boolean isPeerConnectionReady() {
        return _peerConnection != null;
    }

    public void setPeerConnectionObserver(PeerConnection.Observer peerConnectionObserver) {
        _peerConnectionObserver = peerConnectionObserver;
    }

    public void setSdpObserver(SdpObserver sdpObserver) {
        _sdpObserver = sdpObserver;
    }

    public void setLocalDescription(SessionDescription sdp) {
        if(_peerConnection != null)
            _peerConnection.setLocalDescription(_sdpObserver, sdp);
    }

    public SessionDescription getLocalDescription() {
        return _peerConnection.getLocalDescription();
    }

    public boolean hasLocalDescription() {
        return _peerConnection.getLocalDescription() != null;
    }

    public void setRemoteDescription(SessionDescription sdp) {
        if(_peerConnection != null)
            _peerConnection.setRemoteDescription(_sdpObserver, sdp);
    }

    public boolean hasRemoteDescription() {
        return _peerConnection.getRemoteDescription() != null;
    }

    public void createOffer(MediaConstraints sdpMediaConstraints) {
        if(_peerConnection != null)
            _peerConnection.createOffer(_sdpObserver, sdpMediaConstraints);
    }

    public void createAnswer(MediaConstraints sdpMediaConstraints) {
        _peerConnection.createAnswer(_sdpObserver, sdpMediaConstraints);
    }

    public void addIceCandidate(IceCandidate iceCandidate) {
        if (_queuedRemoteCandidates != null) {
            _queuedRemoteCandidates.add(iceCandidate);
        } else {
            _peerConnection.addIceCandidate(iceCandidate);
        }
    }

    public void removeIceCandidates(IceCandidate[] iceCandidates) {
        _peerConnection.removeIceCandidates(iceCandidates);
    }

    public void drainCandidates() {
        if (_queuedRemoteCandidates != null) {
            Log.d(TAG, "Add " + _queuedRemoteCandidates.size() + " remote candidates");
            for (IceCandidate candidate : _queuedRemoteCandidates) {
                _peerConnection.addIceCandidate(candidate);
            }
            _queuedRemoteCandidates = null;
        }
    }

    public void addStream(MediaStream mediaStream) {
        _peerConnection.addStream(mediaStream);
    }

    public void removeStream(MediaStream mediaStream) {
        _peerConnection.removeStream(mediaStream);
    }

    public RtpSender findVideoSender() {
        for (RtpSender sender : _peerConnection.getSenders()) {
            if (sender.track() != null) {
                String trackType = sender.track().kind();
                if (trackType.equals(VIDEO_TRACK_TYPE)) {
                    return sender;
                }
            }
        }

        return null;
    }

    public void setRemoteVideoTrack(VideoTrack videoTrack) {
        _remoteVideoTrack = videoTrack;
    }

    public boolean isCaller() {
        return _isCaller;
    }

    public void setRemoteRenderProxy(UniboProxyRenderer remoteRenderProxy) {
        _remoteRenderProxy = remoteRenderProxy;
        _remoteVideoTrack.addRenderer(new VideoRenderer(_remoteRenderProxy));
    }

    public boolean isMatch(Room room, User targetUser) {
        return room.equals(_room) && targetUser.equals(_targetUser);
    }
}
