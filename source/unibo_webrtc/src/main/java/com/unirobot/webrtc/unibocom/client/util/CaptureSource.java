package com.unirobot.webrtc.unibocom.client.util;

import org.webrtc.Camera1Enumerator;
import org.webrtc.CameraEnumerator;
import org.webrtc.VideoCapturer;

/**
 * Created by San Vo on 14/03/2018.
 */

public class CaptureSource {
    public static VideoCapturer createVideoCapturer() {
        return createCameraCapturer(new Camera1Enumerator(false));
    }

    private static VideoCapturer createCameraCapturer(CameraEnumerator enumerator) {
        final String[] deviceNames = enumerator.getDeviceNames();

        // First, try to find front facing camera
        //Looking for front facing cameras
        for (String deviceName : deviceNames) {
            if (enumerator.isFrontFacing(deviceName)) {
                //Creating front facing camera capturer
                VideoCapturer videoCapturer = enumerator.createCapturer(deviceName, null);

                if (videoCapturer != null) {
                    return videoCapturer;
                }
            }
        }

        // Front facing camera not found, try something else
        //Looking for other cameras
        for (String deviceName : deviceNames) {
            if (!enumerator.isFrontFacing(deviceName)) {
                //Creating other camera capturer
                VideoCapturer videoCapturer = enumerator.createCapturer(deviceName, null);

                if (videoCapturer != null) {
                    return videoCapturer;
                }
            }
        }

        return null;
    }
}
