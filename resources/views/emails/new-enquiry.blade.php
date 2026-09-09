<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color:#333; max-width:600px; margin:0 auto;">
    <h2>New Enquiry #{{ $enquiry->id }}</h2>

    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
        <tr><td style="padding:4px 0;"><strong>Name:</strong></td><td>{{ $enquiry->name }}</td></tr>
        @if($enquiry->company_name)
        <tr><td style="padding:4px 0;"><strong>Company:</strong></td><td>{{ $enquiry->company_name }}</td></tr>
        @endif
        <tr><td style="padding:4px 0;"><strong>Email:</strong></td><td>{{ $enquiry->email }}</td></tr>
        <tr><td style="padding:4px 0;"><strong>Phone:</strong></td><td>{{ $enquiry->phone }}</td></tr>
        @if($enquiry->delivery_location)
        <tr><td style="padding:4px 0;"><strong>Delivery Location:</strong></td><td>{{ $enquiry->delivery_location }}</td></tr>
        @endif
        @if($enquiry->required_delivery_date)
        <tr><td style="padding:4px 0;"><strong>Required By:</strong></td><td>{{ $enquiry->required_delivery_date->format('d M Y') }}</td></tr>
        @endif
    </table>

    @if($enquiry->message)
    <p><strong>Message:</strong><br>{{ $enquiry->message }}</p>
    @endif

    @if($enquiry->items->isNotEmpty())
    <h3>Products</h3>
    <table style="width:100%; border-collapse:collapse;" border="1" cellpadding="6">
        <thead>
            <tr style="background:#f5f5f5;">
                <th align="left">Product</th>
                <th align="left">SKU</th>
                <th align="left">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enquiry->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? 'Product removed' }}</td>
                    <td>{{ $item->product?->sku }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <p style="margin-top:20px;">
        <a href="{{ route('admin.enquiries.show', $enquiry) }}" style="background:#0d6efd; color:#fff; padding:10px 16px; text-decoration:none; border-radius:4px;">
            View in Admin
        </a>
    </p>
</body>
</html>
