class button:
	def __init__(self):
		self.props = ('style')
		self.template = '<div class="button-element" style="{{style}}"><slot></slot></div>'
	def data(self):
		return None
