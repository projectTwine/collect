import { camelCase, throttle } from "lodash";
import { Client, addOns } from "packages/analytics/Keen";
import globalProps from "packages/analytics/properties/globalProps";
import { isInView } from "packages/utils/index";

class Analytics {

  constructor(config) {
    this.config = config;
    this.Client = new Client(config);
    this.ads = [];

    if (config.trackImpressions) {
      this.trackImpressions();
    }
    this.trackClicks();
  }

  getTrackingProps(eventTrackingProps) {
    return {
      ...globalProps(),
      ...this.config.globalProps || null,
      ...eventTrackingProps || null,
      keen: { addons: addOns() },
    };
  }

  track(eventName, eventTrackingProps) {
    const trackingProps = this.getTrackingProps(eventTrackingProps);
    const methodName = camelCase(eventName);

    if (this[methodName]) {
      this[methodName](trackingProps);
    }

    if (this.Client) {
      this.Client.addEvent(eventName, trackingProps);
    }
  }

  trackClicks() {
    const fireClick = (target) => {
      try {
        const trackProps = JSON.parse(target.dataset.trackProps);

        window.ga("send", "event",
          target.dataset.trackClick,
          trackProps.type,
          trackProps.title,
        );
        this.track(
          target.dataset.trackClick,
          trackProps,
        );
      } catch (e) {
        this.track("CLICK_PROP_ERROR", target.dataset.trackClick);
      }
    };

    document.addEventListener("DOMContentLoaded", () => {
      document.addEventListener("mousedown", function handler(event) {
        for (let target = event.target; target && target !== this; target = target.parentNode) {
          if (target.matches("[data-track-click]")) {
            fireClick.call(target, target);
            break;
          }
        }
      }, false);
    });
  }

  trackImpressions() {
    document.addEventListener("DOMContentLoaded", () => {
      this.getTrackImpressionNodes();
    });

    const fireImpression = throttle(() => {
      if (this.impressionNodes && this.impressionNodes.length) {
        this.impressionNodes.forEach((node, index) => {
          if (isInView(node) && this.impressionNodes[index].hasAttribute("data-track-impression")) {
            try {
              this.track(node.dataset.trackImpression, JSON.parse(node.dataset.trackProps));
            } catch (e) {
              this.track("IMPRESSION_PROP_ERROR", node.dataset.trackImpression);
            }
            this.impressionNodes[index].removeAttribute("data-track-impression");
          }
        });
      }
    }, 200);

    window.addEventListener("scroll", fireImpression);
    window.addEventListener("resize", fireImpression);
  }

  getTrackImpressionNodes() {
    const nodes = document.querySelectorAll("[data-track-impression]");
    this.impressionNodes = [].slice.call(nodes, 0);
    window.dispatchEvent(new Event("scroll"));

    return this.impressionNodes;
  }

  adClick(trackingProps) {
    window.ga("send", "event",
      "Ad Clicks - By Provider",
      trackingProps.ad.provider,
      trackingProps.ad.title.concat(" | ", trackingProps.ad.unit),
    );
    window.ga("send", "event",
      "Ad Clicks - By Ad Unit",
      trackingProps.ad.unit,
      trackingProps.ad.title,
    );
  }
}

export default Analytics;
