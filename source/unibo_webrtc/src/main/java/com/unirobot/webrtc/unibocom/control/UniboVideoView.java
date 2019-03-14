package com.unirobot.webrtc.unibocom.control;

import android.content.Context;
import android.util.AttributeSet;

import org.webrtc.SurfaceViewRenderer;

/**
 * Created by San Vo on 14/03/2018.
 */

public class UniboVideoView extends SurfaceViewRenderer {
    public UniboVideoView(Context context) {
        super(context);
    }

    public UniboVideoView(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    public void clear() {
        this.clearImage();
    }
}
