package com.unirobot.webrtc.unibocom.client;

import android.content.Context;
import android.os.Environment;
import android.os.ParcelFileDescriptor;
import android.util.Log;

import com.unirobot.webrtc.unibocom.MessageConst;
import com.unirobot.webrtc.unibocom.client.object.PeerSession;
import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;
import com.unirobot.webrtc.unibocom.client.util.CaptureSource;
import com.unirobot.webrtc.unibocom.client.util.Codec;
import com.unirobot.webrtc.unibocom.configuration.StreamConfiguration;
import com.unirobot.webrtc.unibocom.control.UniboProxyRenderer;
import com.unirobot.webrtc.unibocom.signaling.SignalHandler;
import com.unirobot.webrtc.unibocom.signaling.callback.WebRTCHandlerDelegate;

import org.json.JSONException;
import org.webrtc.AudioSource;
import org.webrtc.AudioTrack;
import org.webrtc.CameraVideoCapturer;
import org.webrtc.DataChannel;
import org.webrtc.EglBase;
import org.webrtc.IceCandidate;
import org.webrtc.MediaConstraints;
import org.webrtc.MediaStream;
import org.webrtc.PeerConnection;
import org.webrtc.PeerConnectionFactory;
import org.webrtc.RtpParameters;
import org.webrtc.RtpReceiver;
import org.webrtc.RtpSender;
import org.webrtc.SdpObserver;
import org.webrtc.SessionDescription;
import org.webrtc.VideoCapturer;
import org.webrtc.VideoRenderer;
import org.webrtc.VideoSource;
import org.webrtc.VideoTrack;
import org.webrtc.voiceengine.WebRtcAudioManager;
import org.webrtc.voiceengine.WebRtcAudioRecord;
import org.webrtc.voiceengine.WebRtcAudioTrack;
import org.webrtc.voiceengine.WebRtcAudioUtils;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

/**
 * Created by San Vo on 28/02/2018.
 */

public class WebRTCHandler {
    private static final String TAG = "Unibo - WebRTCHandler";

    private static final String DISABLE_WEBRTC_AGC_FIELDTRIAL = "WebRTC-Audio-MinimizeResamplingOnMobile/Enabled/";

    private static final String VIDEO_FLEXFEC_FIELDTRIAL = "WebRTC-FlexFEC-03-Advertised/Enabled/WebRTC-FlexFEC-03/Enabled/";
    private static final String VIDEO_VP8_INTEL_HW_ENCODER_FIELDTRIAL = "WebRTC-IntelVP8/Enabled/";
    private static final String VIDEO_H264_HIGH_PROFILE_FIELDTRIAL = "WebRTC-H264HighProfile/Enabled/";

    public static final String VIDEO_TRACK_ID = "ARDAMSv0";
    public static final String AUDIO_TRACK_ID = "ARDAMSa0";

    private static final String AUDIO_ECHO_CANCELLATION_CONSTRAINT = "googEchoCancellation";
    private static final String AUDIO_AUTO_GAIN_CONTROL_CONSTRAINT = "googAutoGainControl";
    private static final String AUDIO_HIGH_PASS_FILTER_CONSTRAINT = "googHighpassFilter";
    private static final String AUDIO_NOISE_SUPPRESSION_CONSTRAINT = "googNoiseSuppression";
    private static final String AUDIO_LEVEL_CONTROL_CONSTRAINT = "levelControl";

    private static final String VIDEO_CODEC_VP8 = "VP8";
    private static final String VIDEO_CODEC_VP9 = "VP9";
    private static final String VIDEO_CODEC_H264 = "H264";

    private static final String AUDIO_CODEC_OPUS = "opus";
    private static final String AUDIO_CODEC_ISAC = "ISAC";

    private static final String OFFER_TO_RECEIVE_AUDIO_CONSTRAINT = "OfferToReceiveAudio";
    private static final String OFFER_TO_RECEIVE_VIDEO_CONSTRAINT = "OfferToReceiveVideo";

    private static final String CONSTRAINT_VALUE_YES = "true";
    private static final String CONSTRAINT_VALUE_NO = "false";

    private WebRTCHandlerDelegate _webRTCHandlerDelegate;

    private SignalHandler _signalHandler;

    private List<PeerConnection.IceServer> _stunServers;
    private StreamConfiguration _streamConfiguration;

    private PeerConnectionFactory _peerConnectionFactory;

    private EglBase _rootEglBase;
    private String _preferredVideoCodec;

    private RtpSender _localVideoSender;
    private MediaStream _localMediaStream;
    private VideoTrack _localVideoTrack;
    private AudioTrack _localAudioTrack;
    private AudioSource _localAudioSource;
    private VideoSource _localVideoSource;
    private MediaConstraints _localAudioConstraints;
    private MediaConstraints _sdpMediaConstraints;

    private VideoCapturer _videoCapturer;
    private boolean _isVideoCapturerStopped;

    private boolean _isEnableAudioRender;
    private boolean _isEnableVideoRender;

    private UniboProxyRenderer _localRenderProxy;

    private PeerSession _peerSession;

    private boolean _isError;

    private boolean _isAudioOnly;

    public WebRTCHandler(WebRTCHandlerDelegate webRTCHandlerDelegate, SignalHandler signalHandler) {
        _signalHandler = signalHandler;
        _webRTCHandlerDelegate = webRTCHandlerDelegate;

        _stunServers = new ArrayList<>();
    }

    public void setStunUrls(List<String> stunUrls) {
        for (String stunUrl : stunUrls)
            _stunServers.add(new PeerConnection.IceServer(stunUrl));
    }

    public void setStreamConfiguration(StreamConfiguration streamConfiguration) {
        _streamConfiguration = streamConfiguration;
    }

    public void connectTo(Room room, User user, boolean isCaller) {
        _peerSession = new PeerSession(room, user, isCaller);

        createPeerConnectionInternal();
    }

    public void createOffer() {
        if (_peerSession != null && _peerSession.isPeerConnectionReady() && !_isError) {
            _peerSession.createOffer(_sdpMediaConstraints);
        }
    }

    public void createAnswer(Room room, User caller, String remoteSdp) {
        if(!_peerSession.getRoom().equals(room) || !_peerSession.getTargetUser().equals(caller)) {
            Log.w(TAG, "createAnswer - Taget does not match");
            return;
        }

        if (_peerSession != null && _peerSession.isPeerConnectionReady() && !_isError) {
            SessionDescription remoteSdpObj = new SessionDescription(SessionDescription.Type.OFFER, remoteSdp);
            setRemoteDescription(remoteSdpObj);

            _peerSession.createAnswer(_sdpMediaConstraints);
        }
    }

    public void setAnswer(Room room, User acceptor, String sdp) {
        if(!_peerSession.getRoom().equals(room) || !_peerSession.getTargetUser().equals(acceptor)) {
            Log.w(TAG, "setAnswer - Taget does not match");
            return;
        }

        SessionDescription remoteSdpObj = new SessionDescription(SessionDescription.Type.ANSWER, sdp);
        setRemoteDescription(remoteSdpObj);
    }

    public void addRemoteIceCandidate(Room room, User acceptor, int sdpMLineIndex, String sdpMid, String candidate) {
        if(_peerSession == null || !_peerSession.getRoom().equals(room) || !_peerSession.getTargetUser().equals(acceptor)) {
            Log.w(TAG, "addRemoteIceCandidate - Taget does not match");
            return;
        }

        if (_peerSession != null && _peerSession.isPeerConnectionReady() && !_isError) {
            IceCandidate iceCandidate = new IceCandidate(sdpMid, sdpMLineIndex, candidate);

            _peerSession.addIceCandidate(iceCandidate);
        }
    }

    public void setLocalRenderProxy(UniboProxyRenderer localRenderProxy) {
        _localRenderProxy = localRenderProxy;
    }

    public boolean setRemoteRenderProxy(Room room, User targetUser, UniboProxyRenderer remoteRenderProxy) {
        if(_peerSession != null && _peerSession.isMatch(room, targetUser)) {
            _peerSession.setRemoteRenderProxy(remoteRenderProxy);
            return true;
        }
        else {
            return false;
        }
    }

    public void initLocalMediaSource() {
        Log.d(TAG, "Init Video Source");
        if(_videoCapturer == null) {
            if(!_isAudioOnly) {
                _isVideoCapturerStopped = true;
                _videoCapturer = CaptureSource.createVideoCapturer();
            }
            createMediaConstraintsInternal();
            createLocalMediaStream();
        }

        if (_videoCapturer == null) {
            if (_webRTCHandlerDelegate != null)
                _webRTCHandlerDelegate.onWebRTCHandlerError(MessageConst.webrtc_camera_fail);
        }
    }

    public void stopVideoSource() {
        if (_videoCapturer != null && !_isVideoCapturerStopped) {
            Log.d(TAG, "Stop video source.");
            try {
                _videoCapturer.stopCapture();
            } catch (InterruptedException e) {
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(e.getMessage());
            }
            _isVideoCapturerStopped = true;
        }
    }

    public void startVideoSource() {
        if (_videoCapturer != null && _isVideoCapturerStopped) {
            Log.d(TAG, "Restart video source.");
            _videoCapturer.startCapture(_streamConfiguration.getVideoWidth(), _streamConfiguration.getVideoHeight(), _streamConfiguration.getVideoFps());
            _isVideoCapturerStopped = false;
        }
    }

    public void createPeerConnectionFactoryInternal(Context context, EglBase rootEglBase) {
        _isAudioOnly = _streamConfiguration.isAudioOnly();
        _rootEglBase = rootEglBase;

        _isError = false;
        _peerConnectionFactory = null;
        _isVideoCapturerStopped = false;
        _localMediaStream = null;
        _isEnableVideoRender = true;
        _localVideoTrack = null;
        _localVideoSender = null;
        _isEnableAudioRender = true;
        _localAudioTrack = null;

        if(_peerSession != null)
            _peerSession.clearPeerSession();

        PeerConnectionFactory.initializeInternalTracer();
        if (_streamConfiguration.isTracing()) {
            PeerConnectionFactory.startInternalTracingCapture(
                    Environment.getExternalStorageDirectory().getAbsolutePath() + File.separator
                            + "unibo-android-webrtc-trace.log");
        }

        Log.d(TAG,"Create peer connection factory. Use video: " + !_isAudioOnly);

        // Initialize field trials.
        String fieldTrials = "";
        if (_streamConfiguration.isVideoFlexfecEnabled()) {
            fieldTrials += VIDEO_FLEXFEC_FIELDTRIAL;
            Log.d(TAG, "Enable FlexFEC field trial.");
        }
        fieldTrials += VIDEO_VP8_INTEL_HW_ENCODER_FIELDTRIAL;
        if (_streamConfiguration.isDisableWebRtcAGCAndHPF()) {
            fieldTrials += DISABLE_WEBRTC_AGC_FIELDTRIAL;
            Log.d(TAG, "Disable WebRTC AGC field trial.");
        }

        // Check preferred video codec.
        _preferredVideoCodec = VIDEO_CODEC_VP8;
        if (!_isAudioOnly && _streamConfiguration.getVideoCodec() != null) {
            switch (_streamConfiguration.getVideoCodec()) {
                case VP8:
                    _preferredVideoCodec = VIDEO_CODEC_VP8;
                    break;
                case VP9:
                    _preferredVideoCodec = VIDEO_CODEC_VP9;
                    break;
                case H264_BASELINE:
                    _preferredVideoCodec = VIDEO_CODEC_H264;
                    break;
                case H264_HIGH:
                    // TODO(magjed): Strip High from SDP when selecting Baseline instead of using field trial.
                    fieldTrials += VIDEO_H264_HIGH_PROFILE_FIELDTRIAL;
                    _preferredVideoCodec = VIDEO_CODEC_H264;
                    break;
                default:
                    _preferredVideoCodec = VIDEO_CODEC_VP8;
            }
        }

        Log.d(TAG, "Preferred video codec: " + _preferredVideoCodec);
        PeerConnectionFactory.initializeFieldTrials(fieldTrials);
        Log.d(TAG, "Field trials: " + fieldTrials);

        // Enable/disable OpenSL ES playback.
        if (!_streamConfiguration.isUseOpenSLES()) {
            Log.d(TAG, "Disable OpenSL ES audio even if device supports it");
            WebRtcAudioManager.setBlacklistDeviceForOpenSLESUsage(true /* enable */);
        } else {
            Log.d(TAG, "Allow OpenSL ES audio if device supports it");
            WebRtcAudioManager.setBlacklistDeviceForOpenSLESUsage(false);
        }

        if (_streamConfiguration.isDisableBuiltInAEC()) {
            Log.d(TAG, "Disable built-in AEC even if device supports it");
            WebRtcAudioUtils.setWebRtcBasedAcousticEchoCanceler(true);
        } else {
            Log.d(TAG, "Enable built-in AEC if device supports it");
            WebRtcAudioUtils.setWebRtcBasedAcousticEchoCanceler(false);
        }

        if (_streamConfiguration.isDisableBuiltInAGC()) {
            Log.d(TAG, "Disable built-in AGC even if device supports it");
            WebRtcAudioUtils.setWebRtcBasedAutomaticGainControl(true);
        } else {
            Log.d(TAG, "Enable built-in AGC if device supports it");
            WebRtcAudioUtils.setWebRtcBasedAutomaticGainControl(false);
        }

        if (_streamConfiguration.isDisableBuiltInNS()) {
            Log.d(TAG, "Disable built-in NS even if device supports it");
            WebRtcAudioUtils.setWebRtcBasedNoiseSuppressor(true);
        } else {
            Log.d(TAG, "Enable built-in NS if device supports it");
            WebRtcAudioUtils.setWebRtcBasedNoiseSuppressor(false);
        }

        // Set audio record error callbacks.
        WebRtcAudioRecord.setErrorCallback(new WebRtcAudioRecord.WebRtcAudioRecordErrorCallback() {
            @Override
            public void onWebRtcAudioRecordInitError(String errorMessage) {
                _isError = true;
                Log.e(TAG, "onWebRtcAudioRecordInitError: " + errorMessage);
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(errorMessage);
            }

            @Override
            public void onWebRtcAudioRecordStartError(
                    WebRtcAudioRecord.AudioRecordStartErrorCode errorCode, String errorMessage) {
                _isError = true;
                Log.e(TAG, "onWebRtcAudioRecordStartError: " + errorCode + ". " + errorMessage);
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(errorMessage);
            }

            @Override
            public void onWebRtcAudioRecordError(String errorMessage) {
                _isError = true;
                Log.e(TAG, "onWebRtcAudioRecordError: " + errorMessage);
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(errorMessage);
            }
        });

        WebRtcAudioTrack.setErrorCallback(new WebRtcAudioTrack.WebRtcAudioTrackErrorCallback() {
            @Override
            public void onWebRtcAudioTrackInitError(String errorMessage) {
                _isError = true;
                Log.e(TAG, "onWebRtcAudioTrackInitError: " + errorMessage);
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(errorMessage);
            }

            @Override
            public void onWebRtcAudioTrackStartError(String errorMessage) {
                _isError = true;
                Log.e(TAG, "onWebRtcAudioTrackStartError: " + errorMessage);
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(errorMessage);
            }

            @Override
            public void onWebRtcAudioTrackError(String errorMessage) {
                _isError = true;
                Log.e(TAG, "onWebRtcAudioTrackError: " + errorMessage);
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(errorMessage);
            }
        });

        // Create peer connection factory.
        PeerConnectionFactory.initializeAndroidGlobals(
                context, _streamConfiguration.isVideoCodecHwAcceleration());

        _peerConnectionFactory = new PeerConnectionFactory(null);

        Log.d(TAG, "Peer connection factory created.");

        if (!_isAudioOnly) {
            _peerConnectionFactory.setVideoHwAccelerationOptions(_rootEglBase.getEglBaseContext(), _rootEglBase.getEglBaseContext());
        }
    }

    public void setRemoteDescription(SessionDescription sdp) {
        if (_peerSession != null && _peerSession.isPeerConnectionReady() && !_isError) {
            String sdpDescription = sdp.description;
            boolean preferIsac = _streamConfiguration.getAudioCodec() != null
                    && _streamConfiguration.getAudioCodec().equals(StreamConfiguration.AudioCodec.ISAC);
            if (preferIsac) {
                sdpDescription = Codec.preferCodec(sdpDescription, AUDIO_CODEC_ISAC, true);
            }
            if (!_isAudioOnly) {
                sdpDescription = Codec.preferCodec(sdpDescription, _preferredVideoCodec, false);
            }
            SessionDescription sdpRemote = new SessionDescription(sdp.type, sdpDescription);
            _peerSession.setRemoteDescription(sdpRemote);
        }
    }

    public void setAudioEnabled(boolean enable) {
        _isEnableAudioRender = enable;
        if (_localAudioTrack != null)
            _localAudioTrack.setEnabled(_isEnableAudioRender);
    }

    public void setVideoEnabled(boolean enable) {
        _isEnableVideoRender = enable;
        if (_localVideoTrack != null) {
            _localVideoTrack.setEnabled(_isEnableVideoRender);
        }
//        if (_remoteVideoTrack != null) {
//            _remoteVideoTrack.setEnabled(_isEnableVideoRender);
//        }
    }

    public void switchCamera() {
        if (_videoCapturer instanceof CameraVideoCapturer) {
            if (!_isEnableVideoRender || _isError || _videoCapturer == null) {
                Log.e(TAG, "Failed to switch camera. Video: " + _isEnableVideoRender + ". Error : " + _isError);
                return; // No video is sent or only one camera is available or error happened.
            }
            Log.d(TAG, "Switch camera");
            CameraVideoCapturer cameraVideoCapturer = (CameraVideoCapturer) _videoCapturer;
            cameraVideoCapturer.switchCamera(null);
        } else {
            Log.d(TAG, "Will not switch camera, video caputurer is not a camera");
        }
    }

    public void setVideoMaxBitrate(int maxBitrate) {
        if (_peerSession == null || !_peerSession.isPeerConnectionReady() || _localVideoSender == null || _isError) {
            return;
        }
        Log.d(TAG, "Requested max video bitrate: " + maxBitrate);
        if (_localVideoSender == null) {
            Log.w(TAG, "Sender is not ready.");
            return;
        }

        RtpParameters parameters = _localVideoSender.getParameters();
        if (parameters.encodings.size() == 0) {
            Log.w(TAG, "RtpParameters are not ready.");
            return;
        }

        for (RtpParameters.Encoding encoding : parameters.encodings) {
            // Null value means no limit.
            encoding.maxBitrateBps = maxBitrate;
        }
        if (!_localVideoSender.setParameters(parameters)) {
            Log.e(TAG, "RtpSender.setParameters failed.");
        }
        Log.d(TAG, "Configured max video bitrate to: " + maxBitrate);
    }


    public void closePeerConnection() {
        Log.d(TAG, "Closing peer connection.");

        if (_peerSession != null && _peerSession.isPeerConnectionReady()) {
            _peerSession.releasePeerConnection(_localMediaStream);
        }
    }

    public void close() throws InterruptedException {
        closePeerConnection();

        Log.d(TAG, "Stopping capture.");
        if (_videoCapturer != null) {
            _videoCapturer.stopCapture();
            _isVideoCapturerStopped = true;
        }
        _localRenderProxy = null;
    }

    public void release() throws InterruptedException {
        close();

        Log.d(TAG, "Closing audio source.");
        if (_localAudioSource != null) {
            _localAudioSource.dispose();
            _localAudioSource = null;
        }

        Log.d(TAG, "Closing capture.");
        if (_videoCapturer != null) {
            _videoCapturer.dispose();
            _videoCapturer = null;
        }
        Log.d(TAG, "Closing video source.");
        if (_localVideoSource != null) {
            _localVideoSource.dispose();
            _localVideoSource = null;
        }

        if (_peerConnectionFactory != null && _streamConfiguration.isAecDump()) {
            _peerConnectionFactory.stopAecDump();
        }
        Log.d(TAG, "Closing peer connection factory.");
        if (_peerConnectionFactory != null) {
            _peerConnectionFactory.dispose();
            _peerConnectionFactory = null;
        }
        Log.d(TAG, "Closing peer connection done.");

        PeerConnectionFactory.stopInternalTracingCapture();
        PeerConnectionFactory.shutdownInternalTracer();
    }

    public PeerSession getPeerSession() {
        return _peerSession;
    }

    private void createMediaConstraintsInternal() {
        // Check if there is a camera on device and disable video call if not.
        if (_videoCapturer == null) {
            Log.w(TAG, "No camera on device. Switch to audio only call.");
            _isAudioOnly = true;
        }

        // Create audio constraints.
        _localAudioConstraints = new MediaConstraints();
        // added for audio performance measurements
        if (_streamConfiguration.isNoAudioProcessing()) {
            Log.d(TAG, "Disabling audio processing");
            _localAudioConstraints.mandatory.add(
                    new MediaConstraints.KeyValuePair(AUDIO_ECHO_CANCELLATION_CONSTRAINT, "false"));
            _localAudioConstraints.mandatory.add(
                    new MediaConstraints.KeyValuePair(AUDIO_AUTO_GAIN_CONTROL_CONSTRAINT, "false"));
            _localAudioConstraints.mandatory.add(
                    new MediaConstraints.KeyValuePair(AUDIO_HIGH_PASS_FILTER_CONSTRAINT, "false"));
            _localAudioConstraints.mandatory.add(
                    new MediaConstraints.KeyValuePair(AUDIO_NOISE_SUPPRESSION_CONSTRAINT, "false"));
        }
        if (_streamConfiguration.isEnableLevelControl()) {
            Log.d(TAG, "Enabling level control.");
            _localAudioConstraints.mandatory.add(
                    new MediaConstraints.KeyValuePair(AUDIO_LEVEL_CONTROL_CONSTRAINT, "true"));
        }
        // Create SDP constraints.
        _sdpMediaConstraints = new MediaConstraints();
        _sdpMediaConstraints.mandatory.add(
                new MediaConstraints.KeyValuePair(OFFER_TO_RECEIVE_AUDIO_CONSTRAINT, CONSTRAINT_VALUE_YES));

        _sdpMediaConstraints.mandatory.add(
                new MediaConstraints.KeyValuePair(OFFER_TO_RECEIVE_VIDEO_CONSTRAINT, _isAudioOnly?CONSTRAINT_VALUE_NO:CONSTRAINT_VALUE_YES));
    }

    private void createPeerConnectionInternal() {
        if (_peerConnectionFactory == null || _isError) {
            Log.e(TAG, "Peerconnection factory is not created");
            return;
        }

        Log.d(TAG, "Create sdp observer.");

        _peerSession.setSdpObserver(new SdpObserver() {
            @Override
            public void onCreateSuccess(SessionDescription sessionDescription) {
                if (_peerSession != null && _peerSession.isPeerConnectionReady()) {
                    if (_peerSession.hasLocalDescription()) {
                        _isError = true;
                        _webRTCHandlerDelegate.onWebRTCHandlerError(MessageConst.webrtc_multi_sdp_created);
                        return;
                    }

                    String sdpDescription = sessionDescription.description;
                    boolean preferIsac = _streamConfiguration.getAudioCodec() != null
                            && _streamConfiguration.getAudioCodec().equals(StreamConfiguration.AudioCodec.ISAC);
                    if (preferIsac) {
                        sdpDescription = Codec.preferCodec(sdpDescription, AUDIO_CODEC_ISAC, true);
                    }
                    if (!_isAudioOnly) {
                        sdpDescription = Codec.preferCodec(sdpDescription, _preferredVideoCodec, false);
                    }
                    SessionDescription sdp = new SessionDescription(sessionDescription.type, sdpDescription);

                    if (_peerSession != null && _peerSession.isPeerConnectionReady() && !_isError)
                        _peerSession.setLocalDescription(sdp);
                }
            }

            @Override
            public void onSetSuccess() {
                if (_peerSession != null && _peerSession.isPeerConnectionReady() && !_isError) {
                    if (_peerSession.isCaller()) {
                        if (!_peerSession.hasRemoteDescription()) { //set LocalDescription success
                            try {
                                _signalHandler.sendOffer(_peerSession.getRoom().getRoomName(), _peerSession.getTargetUser().getUid(), _peerSession.getLocalDescription());
                            } catch (JSONException e) {
                                if (_webRTCHandlerDelegate != null)
                                    _webRTCHandlerDelegate.onWebRTCHandlerError(e.getMessage());
                            }
                            Log.d(TAG, "Local SDP set succesfully");
                        } else {  //set RemoteDescription success
                            Log.d(TAG, "Remote SDP set succesfully");
                            _peerSession.drainCandidates();
                        }
                    } else {
                        if (_peerSession.hasLocalDescription()) {  //set LocalDescription success
                            try {
                                _signalHandler.sendAnswer(_peerSession.getRoom().getRoomName(), _peerSession.getTargetUser().getUid(), _peerSession.getLocalDescription());

                                _peerSession. drainCandidates();
                                Log.d(TAG, "Local SDP set succesfully");
                            } catch (JSONException e) {
                                if (_webRTCHandlerDelegate != null)
                                    _webRTCHandlerDelegate.onWebRTCHandlerError(e.getMessage());
                            }
                        } else {   //set RemoteDescription success
                            Log.d(TAG, "Remote SDP set succesfully");
                        }
                    }
                }
            }

            @Override
            public void onCreateFailure(String s) {
                _isError = true;
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(s);
            }

            @Override
            public void onSetFailure(String s) {
                _isError = true;
                if (_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(s);
            }
        });

        Log.d(TAG, "Create peer connection observer.");

        _peerSession.setPeerConnectionObserver(new PeerConnection.Observer() {
            @Override
            public void onSignalingChange(PeerConnection.SignalingState signalingState) {
                Log.d(TAG, "SignalingState: " + signalingState);
            }

            @Override
            public void onIceConnectionChange(PeerConnection.IceConnectionState iceConnectionState) {
                Log.d(TAG, "IceConnectionState: " + iceConnectionState);
                if (iceConnectionState == PeerConnection.IceConnectionState.CONNECTED) {
                    _webRTCHandlerDelegate.onIceConnected(_peerSession.getRoom(), _peerSession.getTargetUser());
                } else if (iceConnectionState == PeerConnection.IceConnectionState.CLOSED) {
                    _webRTCHandlerDelegate.onIceClosed(_peerSession.getRoom(), _peerSession.getTargetUser());
                } else if (iceConnectionState == PeerConnection.IceConnectionState.FAILED) {
                    _isError = true;
                    if (_webRTCHandlerDelegate != null)
                        _webRTCHandlerDelegate.onWebRTCHandlerError(MessageConst.webrtc_ice_connection_fail);
                }
            }

            @Override
            public void onIceConnectionReceivingChange(boolean receiving) {
                Log.d(TAG, "IceConnectionReceiving changed to " + receiving);
            }

            @Override
            public void onIceGatheringChange(PeerConnection.IceGatheringState iceGatheringState) {
                Log.d(TAG, "IceGatheringState: " + iceGatheringState);
            }

            @Override
            public void onIceCandidate(IceCandidate iceCandidate) {
                try {
                    _signalHandler.sendCandidate(_peerSession.getRoom().getRoomName(), _peerSession.getTargetUser().getUid(), iceCandidate);
                } catch (JSONException e) {
                    if (_webRTCHandlerDelegate != null)
                        _webRTCHandlerDelegate.onWebRTCHandlerError(e.getMessage());
                }
            }

            @Override
            public void onIceCandidatesRemoved(IceCandidate[] iceCandidates) {
                if (_peerSession != null && _peerSession.isPeerConnectionReady() && !_isError) {
                    _peerSession.drainCandidates();
                    _peerSession.removeIceCandidates(iceCandidates);
                }
            }

            @Override
            public void onAddStream(MediaStream mediaStream) {
                if (_peerSession != null && _peerSession.isPeerConnectionReady() && !_isError) {
                    if (mediaStream.audioTracks.size() > 1 || mediaStream.videoTracks.size() > 1) {
                        _isError = true;
                        if (_webRTCHandlerDelegate != null)
                            _webRTCHandlerDelegate.onWebRTCHandlerError(MessageConst.webrtc_stream_weird);
                        return;
                    }
                    if (mediaStream.videoTracks.size() == 1) {
                        VideoTrack remoteVideoTrack = mediaStream.videoTracks.get(0);
                        remoteVideoTrack.setEnabled(_isEnableVideoRender);

                        _peerSession.setRemoteVideoTrack(remoteVideoTrack);
                    }
                }
            }

            @Override
            public void onRemoveStream(MediaStream mediaStream) {
                _peerSession.removeStream(mediaStream);
            }

            @Override
            public void onDataChannel(DataChannel dataChannel) {

            }

            @Override
            public void onRenegotiationNeeded() {

            }

            @Override
            public void onAddTrack(RtpReceiver rtpReceiver, MediaStream[] mediaStreams) {

            }
        });

        if (!_isAudioOnly) {
            Log.d(TAG, "EGLContext: " + _rootEglBase.getEglBaseContext());
            _peerConnectionFactory.setVideoHwAccelerationOptions(_rootEglBase.getEglBaseContext(), _rootEglBase.getEglBaseContext());
        }

        _peerSession.initPeerConnection(_peerConnectionFactory,_stunServers);

        // Set default WebRTC tracing and INFO libjingle logging.
        // NOTE: this _must_ happen while |factory| is alive!

        _peerSession.addStream(_localMediaStream);
        if (!_isAudioOnly) {
            _localVideoSender = _peerSession.findVideoSender();
        }

        if (_streamConfiguration.isAecDump()) {
            try {
                ParcelFileDescriptor aecDumpFileDescriptor =
                        ParcelFileDescriptor.open(new File(Environment.getExternalStorageDirectory().getPath()
                                        + File.separator + "audio.aecdump"),
                                ParcelFileDescriptor.MODE_READ_WRITE | ParcelFileDescriptor.MODE_CREATE
                                        | ParcelFileDescriptor.MODE_TRUNCATE);
                _peerConnectionFactory.startAecDump(aecDumpFileDescriptor.getFd(), -1);
            } catch (IOException e) {
                Log.e(TAG, "Can not open audio.aecdump file in external storage", e);
                if(_webRTCHandlerDelegate != null)
                    _webRTCHandlerDelegate.onWebRTCHandlerError(MessageConst.webrtc_ace_dump_fail);
            }
        }

        Log.d(TAG, "Peer connection created.");
    }

    private void createLocalMediaStream() {
        _localMediaStream = _peerConnectionFactory.createLocalMediaStream("ARDAMS");

        if(!_isAudioOnly) {
            _localVideoSource = _peerConnectionFactory.createVideoSource(_videoCapturer);
            _localVideoTrack = _peerConnectionFactory.createVideoTrack(VIDEO_TRACK_ID, _localVideoSource);
            _localVideoTrack.setEnabled(_isEnableVideoRender);
            _localVideoTrack.addRenderer(new VideoRenderer(_localRenderProxy));
            _localMediaStream.addTrack(_localVideoTrack);
        }

        _localAudioSource = _peerConnectionFactory.createAudioSource(_localAudioConstraints);
        _localAudioTrack = _peerConnectionFactory.createAudioTrack(AUDIO_TRACK_ID, _localAudioSource);
        _localAudioTrack.setEnabled(_isEnableAudioRender);
        _localMediaStream.addTrack(_localAudioTrack);
    }
}