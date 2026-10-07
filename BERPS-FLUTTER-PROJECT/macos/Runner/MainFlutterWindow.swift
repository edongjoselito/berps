import Cocoa
import FlutterMacOS

class MainFlutterWindow: NSWindow {
  override func awakeFromNib() {
    let flutterViewController = FlutterViewController()
    let windowFrame = self.frame
    self.contentViewController = flutterViewController
    self.setFrame(windowFrame, display: true)
    self.minSize = NSSize(width: 1100, height: 700)
    self.title = "BERPS"
    // Unified, transparent title bar: app content runs edge-to-edge under
    // the traffic lights (Flutter reserves AppTheme.titleBarInset for them).
    self.titleVisibility = .hidden
    self.titlebarAppearsTransparent = true
    self.styleMask.insert(.fullSizeContentView)
    if self.frame.width < 1100 || self.frame.height < 700 {
      self.setContentSize(NSSize(width: 1280, height: 800))
      self.center()
    }

    RegisterGeneratedPlugins(registry: flutterViewController)

    super.awakeFromNib()
  }
}
