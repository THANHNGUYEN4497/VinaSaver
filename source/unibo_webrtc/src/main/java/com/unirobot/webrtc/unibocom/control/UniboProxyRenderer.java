package com.unirobot.webrtc.unibocom.control;

import org.webrtc.VideoRenderer;

/**
 * Created by San Vo on 14/03/2018.
 */

public class UniboProxyRenderer implements VideoRenderer.Callbacks {
    private VideoRenderer.Callbacks target;

    synchronized public void renderFrame(VideoRenderer.I420Frame frame) {
        if (target == null) {
            VideoRenderer.renderFrameDone(frame);
            return;
        }

        target.renderFrame(frame);
    }

    synchronized public void renderTo(VideoRenderer.Callbacks target) {
        this.target = target;
    }
}