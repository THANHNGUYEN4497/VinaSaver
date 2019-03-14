package com.unirobot.webrtc.unibocom.configuration;

/**
 * Created by San Vo on 14/03/2018.
 */

public class StreamConfiguration {
    public enum VideoCodec {VP8, VP9, H264_BASELINE, H264_HIGH}
    public enum AudioCodec {OPUS, ISAC}

    private boolean _audioOnly;
    private boolean _tracing;
    private int _videoWidth;
    private int _videoHeight;
    private int _videoFps;
    private VideoCodec _videoCodec;
    private boolean _videoCodecHwAcceleration;
    private boolean _videoFlexfecEnabled;
    private AudioCodec _audioCodec;
    private boolean _noAudioProcessing;
    private boolean _aecDump;
    private boolean _useOpenSLES;
    private boolean _disableBuiltInAEC;
    private boolean _disableBuiltInAGC;
    private boolean _disableBuiltInNS;
    private boolean _enableLevelControl;
    private boolean _disableWebRtcAGCAndHPF;
    //TODO DataChannelParameters
    //private DataChannelParameters dataChannelParameters;

    public StreamConfiguration(boolean audioOnly, boolean tracing,
                                    int videoWidth, int videoHeight, int videoFps, VideoCodec videoCodec,
                                    boolean videoCodecHwAcceleration, boolean videoFlexfecEnabled,
                                    AudioCodec audioCodec, boolean noAudioProcessing, boolean aecDump, boolean useOpenSLES,
                                    boolean disableBuiltInAEC, boolean disableBuiltInAGC, boolean disableBuiltInNS,
                                    boolean enableLevelControl, boolean disableWebRtcAGCAndHPF) {
        _audioOnly = audioOnly;
        _tracing = tracing;
        _videoWidth = videoWidth;
        _videoHeight = videoHeight;
        _videoFps = videoFps;
        _videoCodec = videoCodec;
        _videoFlexfecEnabled = videoFlexfecEnabled;
        _videoCodecHwAcceleration = videoCodecHwAcceleration;
        _audioCodec = audioCodec;
        _noAudioProcessing = noAudioProcessing;
        _aecDump = aecDump;
        _useOpenSLES = useOpenSLES;
        _disableBuiltInAEC = disableBuiltInAEC;
        _disableBuiltInAGC = disableBuiltInAGC;
        _disableBuiltInNS = disableBuiltInNS;
        _enableLevelControl = enableLevelControl;
        _disableWebRtcAGCAndHPF = disableWebRtcAGCAndHPF;
    }

    public boolean isTracing() {
        return _tracing;
    }

    public boolean isVideoFlexfecEnabled() {
        return _videoFlexfecEnabled;
    }

    public boolean isDisableWebRtcAGCAndHPF() {
        return _disableWebRtcAGCAndHPF;
    }

    public boolean isAudioOnly() {
        return _audioOnly;
    }

    public VideoCodec getVideoCodec() {
        return _videoCodec;
    }

    public AudioCodec getAudioCodec() {
        return _audioCodec;
    }

    public boolean isUseOpenSLES() {
        return _useOpenSLES;
    }

    public boolean isDisableBuiltInAEC() {
        return _disableBuiltInAEC;
    }

    public boolean isDisableBuiltInAGC() {
        return _disableBuiltInAGC;
    }

    public boolean isDisableBuiltInNS() {
        return _disableBuiltInNS;
    }

    public boolean isVideoCodecHwAcceleration() {
        return _videoCodecHwAcceleration;
    }

    public boolean isAecDump() {
        return _aecDump;
    }

    public int getVideoWidth() {
        return _videoWidth;
    }

    public int getVideoHeight() {
        return _videoHeight;
    }

    public int getVideoFps() {
        return _videoFps;
    }

    public boolean isNoAudioProcessing() {
        return _noAudioProcessing;
    }

    public boolean isEnableLevelControl() {
        return _enableLevelControl;
    }
}
